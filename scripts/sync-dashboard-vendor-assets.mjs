import { copyFile, mkdir, readdir, readFile, rm } from 'node:fs/promises';
import path from 'node:path';

const projectRoot = process.cwd();
const publicVendorRoot = path.join(projectRoot, 'public', 'vendor', 'dashboard');
const vendorManifestPath = path.join(projectRoot, 'resources', 'vendor', 'dashboard', 'manifest.json');

function resolveProjectPath(relativePath) {
  return path.join(projectRoot, ...relativePath.split('/'));
}

function resolveRuntimePath(runtimePath) {
  return path.join(projectRoot, 'public', ...runtimePath.replace(/^\//, '').split('/'));
}

async function readVendorManifest() {
  const manifestContents = await readFile(vendorManifestPath, 'utf8');
  const manifest = JSON.parse(manifestContents);

  return Array.isArray(manifest.assets) ? manifest.assets : [];
}

async function copyFontAwesomeWebfonts(asset) {
  const sourceDir = resolveProjectPath(asset.source);
  const targetDir = resolveRuntimePath(asset.runtimePath);
  const files = await readdir(sourceDir);

  await mkdir(targetDir, { recursive: true });

  await Promise.all(
    files
      .filter((file) => file.endsWith('.woff2') || file.endsWith('.woff'))
      .map((file) =>
        copyFile(path.join(sourceDir, file), path.join(targetDir, file))
      )
  );
}

async function main() {
  const manifestAssets = await readVendorManifest();
  const fileAssets = manifestAssets.filter((asset) => asset.kind !== 'font-directory');
  const fontDirectoryAsset = manifestAssets.find((asset) => asset.id === 'fontawesome-webfonts');

  await rm(publicVendorRoot, { recursive: true, force: true });

  await Promise.all(
    fileAssets.map(async (asset) => {
      const from = resolveProjectPath(asset.source);
      const to = resolveRuntimePath(asset.runtimePath);

      await mkdir(path.dirname(to), { recursive: true });
      await copyFile(from, to);
    })
  );

  if (fontDirectoryAsset) {
    await copyFontAwesomeWebfonts(fontDirectoryAsset);
  }

  process.stdout.write('Dashboard vendor assets synced.\n');
}

main().catch((error) => {
  process.stderr.write(`${error instanceof Error ? error.stack ?? error.message : String(error)}\n`);
  process.exitCode = 1;
});
