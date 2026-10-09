"""Analisis kepemilikan potongan script bersama (read-only): `python3 scripts/dev/menu-ownership.py`.

Chunk = pernyataan JS tingkat atas di partial script bersama. Sebuah chunk "milik satu menu" bila hanya markup menu itu
(dan chunk lain yang juga hanya milik menu itu) yang memakainya; chunk seperti itu seharusnya hidup di
`shell/menu-script-<menu>.blade.php`. Keluaran: byte yang masih bisa dipindah per menu, dan penghalang terbesar.
"""
import re,os,sys,json,glob
ROOT=os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
BASE=ROOT+'/resources/views/dashboard/partials/'
SH=BASE+'shell/'
def read(p): return open(p).read()

def included_in_order():
    """follow @include('dashboard.partials.shell.X') recursively starting at body-app-assembly (unconditional includes only matter; conditional ones are listed too)."""
    order=[];seen=set()
    def walk(name):
        p=SH+name+'.blade.php'
        if not os.path.exists(p): p=BASE+'scripts/'+name+'.blade.php'
        if not os.path.exists(p): return
        for m in re.finditer(r"@include\('dashboard\.partials\.(shell|scripts)\.([\w-]+)'",read(p)):
            n=m.group(2)
            if n.startswith('app-script-') or n.startswith('menu-scripts-') or n.startswith('export-'):
                if n.startswith('menu-scripts-'): continue
                if n not in seen: seen.add(n); order.append(n); walk(n)
            else:
                pass
    walk('body-app-assembly')
    # export partials live in partials/scripts
    return order

def strip_strings_comments(code):
    """replace string/template/comment content with spaces (keeps length & newlines) to count brackets safely"""
    out=[];i=0;n=len(code)
    while i<n:
        c=code[i]
        if c=='/' and i+1<n and code[i+1]=='/':
            j=code.find('\n',i); j=n if j<0 else j
            out.append(' '*(j-i)); i=j; continue
        if c=='/' and i+1<n and code[i+1]=='*':
            j=code.find('*/',i+2); j=n-2 if j<0 else j
            seg=code[i:j+2]; out.append(re.sub(r'[^\n]',' ',seg)); i=j+2; continue
        if c in '\'"':
            q=c;j=i+1
            while j<n and code[j]!=q:
                if code[j]=='\\': j+=1
                if code[j]=='\n': break
                j+=1
            seg=code[i:j+1]; out.append(re.sub(r'[^\n]',' ',seg)); i=j+1; continue
        if c=='`':
            # template literal: blank text but keep ${...} contents bracket-neutral (treat entire literal as blank)
            j=i+1;depth=0
            while j<n:
                if code[j]=='\\': j+=2; continue
                if code[j]=='`' and depth==0: break
                if code[j]=='$' and j+1<n and code[j+1]=='{': depth+=1; j+=2; continue
                if code[j]=='}' and depth>0: depth-=1
                j+=1
            seg=code[i:j+1]; out.append(re.sub(r'[^\n]',' ',seg)); i=j+1; continue
        out.append(c); i+=1
    return ''.join(out)

def chunk_file(text):
    """returns list of (start_line_idx, end_line_idx, kind) inside verbatim segments. Works on lines."""
    lines=text.split('\n')
    chunks=[];inv=('@verbatim' not in text);cur=None
    clean=strip_strings_comments(text).split('\n')
    depth=0
    for idx,line in enumerate(lines):
        s=line.strip()
        if s=='@verbatim': inv=True; continue
        if s=='@endverbatim':
            if cur: chunks.append(cur); cur=None
            inv=False; depth=0; continue
        if not inv: continue
        cl=clean[idx]
        if cur is None:
            if not s: continue
            cur=[idx,idx]
        else:
            if depth==0 and s:
                # new chunk starts when previous balanced and this line is not a continuation
                if not re.match(r'^[\)\}\]\.\?\:]|^(\|\||&&|\+|-|\*|/(?!/))',s):
                    chunks.append(cur); cur=[idx,idx]
                else: cur[1]=idx
            else:
                if s: cur[1]=idx
        for ch in cl:
            if ch in '({[': depth+=1
            elif ch in ')}]': depth-=1
        if depth<0: depth=0
    if cur: chunks.append(cur)
    # merge comment-only chunks into the next
    merged=[];pend=None
    for a,b in chunks:
        body='\n'.join(lines[a:b+1])
        iscomment=all(l.strip().startswith('//') or not l.strip() for l in lines[a:b+1])
        if iscomment:
            pend=a if pend is None else pend; continue
        merged.append([pend if pend is not None else a,b]); pend=None
    return lines,merged

DECL=re.compile(r'^\s*(?:export\s+)?(?:(?:async\s+)?function\*?\s+([A-Za-z_$][\w$]*)|(?:const|let|var)\s+([A-Za-z_$][\w$]*)|(?:const|let|var)\s*\{([^}]*)\}|(?:const|let|var)\s*\[([^\]]*)\])')
def declared_names(chunk_text):
    first=''
    for l in chunk_text.split('\n'):
        if l.strip() and not l.strip().startswith('//'): first=l; break
    m=DECL.match(first)
    if not m: return []
    if m.group(1): return [m.group(1)]
    if m.group(2): return [m.group(2)]
    names=[]
    grp=m.group(3) or m.group(4)
    for p in grp.split(','):
        p=p.split(':')[-1].split('=')[0].strip()
        if p: names.append(p)
    return names

IDENT=re.compile(r'(?<![\w$.])([A-Za-z_$][\w$]*)')
def idents(code):
    # spread (`...name`) is a reference, not a property access
    return set(IDENT.findall(strip_strings_comments(code).replace('...','   ')))
def raw_idents(code):
    # markup: expressions live inside attribute strings/moustaches, so do not strip strings
    return set(IDENT.findall(code.replace('...','   ')))

def main():
    order=included_in_order()
    files=[]
    for n in order:
        p=SH+n+'.blade.php'
        if not os.path.exists(p): p=BASE+'scripts/'+n+'.blade.php'
        if os.path.exists(p): files.append((n,p))
    # also export partials in scripts dir
    chunks=[]  # dict
    for n,p in files:
        if n in ('app-script-open','app-script-close','app-script-return-block'): continue
        text=read(p)
        lines,ch=chunk_file(text)
        for a,b in ch:
            t='\n'.join(lines[a:b+1])
            chunks.append({'file':n,'a':a,'b':b,'text':t,'bytes':len(t),'names':declared_names(t)})
    byname={}
    for i,c in enumerate(chunks):
        for nm in c['names']: byname.setdefault(nm,[]).append(i)
    dups={k:v for k,v in byname.items() if len(v)>1}
    # references
    for i,c in enumerate(chunks):
        ids=idents(c['text'])
        c['refs']={byname[n][0] for n in ids if n in byname and byname[n][0]!=i and n not in c['names']}
        c['named']=bool(c['names'])
    # menu markup refs
    menus={}
    for p in glob.glob(BASE+'menus/*.blade.php'):
        menus[os.path.basename(p)[:-10]]=raw_idents(read(p))
    shared_markup={}
    for p in glob.glob(SH+'*.blade.php'):
        b=os.path.basename(p)[:-10]
        if b.startswith('app-script-') or b.startswith('menu-scripts-') or b.startswith('body-'): continue
        shared_markup[b]=raw_idents(read(p))
    for p in glob.glob(BASE+'scripts/*.blade.php'):
        pass
    ALL='*ALL*'
    own=[set() for _ in chunks]
    for i,c in enumerate(chunks):
        for nm in c['names']:
            for m,ids in menus.items():
                if nm in ids: own[i].add(m)
            for m,ids in shared_markup.items():
                if nm in ids: own[i].add(ALL)
    # consumers
    consumers=[set() for _ in chunks]
    for i,c in enumerate(chunks):
        for j in c['refs']: consumers[j].add(i)
    # unnamed chunks always run -> ALL for their references
    changed=True
    for i,c in enumerate(chunks):
        if not c['named']:
            for j in c['refs']: own[j].add(ALL)
    while changed:
        changed=False
        for i in range(len(chunks)):
            new=set(own[i])
            for d in consumers[i]:
                if chunks[d]['named']:
                    # a named chunk nobody consumes stays where it is (could be called dynamically): whatever it references must stay visible everywhere
                    new|=own[d] if own[d] else {ALL}
            if new!=own[i]: own[i]=new; changed=True
    result={}
    movable=0;shared=0;unused=0
    for i,c in enumerate(chunks):
        o=own[i]
        if not c['named']: kind='unnamed'
        elif not o: kind='unused'; unused+=c['bytes']
        elif ALL in o or len(o)>1: kind='shared'; shared+=c['bytes']
        else: kind='menu:'+next(iter(o)); movable+=c['bytes']
        c['kind']=kind
    total=sum(c['bytes'] for c in chunks)
    unnamed=sum(c['bytes'] for c in chunks if c['kind']=='unnamed')
    print(f'files={len(files)} chunks={len(chunks)} total={total}B  movable-to-single-menu={movable}B  shared={shared}B  unused(no consumer)={unused}B  unnamed={unnamed}B  dupnames={len(dups)}')
    per={}
    for c in chunks:
        if c['kind'].startswith('menu:'): per.setdefault(c['kind'][5:],[]).append(c)
    for m,cs in sorted(per.items(),key=lambda x:-sum(c['bytes'] for c in x[1])):
        print(f'  {m:26s} {sum(c["bytes"] for c in cs):7d}B in {len(cs):3d} chunks  files={sorted({c["file"][11:] for c in cs})[:5]}')
    # blockers: shared chunks whose ownership is {single menu} + ALL due to unnamed/infra consumers
    block={}
    for i,c in enumerate(chunks):
        if c['kind']=='shared' and ALL in own[i]:
            menus_only=[m for m in own[i] if m!=ALL]
            if len(menus_only)<=1:
                for d in consumers[i]:
                    if not chunks[d]['named']: block[(c['file'],chunks[d]['file'])]=block.get((c['file'],chunks[d]['file']),0)+c['bytes']
    print('top blockers (shared chunk owned by <=1 menu but referenced by an always-run statement):')
    for (f,g),b in sorted(block.items(),key=lambda x:-x[1])[:12]: print(f'   {f[11:]:40s} blocked by unnamed stmts in {g[11:]:34s} {b}B')
    return chunks,own
if __name__=='__main__': main()
