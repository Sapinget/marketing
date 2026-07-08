import { copyFile, mkdir, readdir, rm } from 'node:fs/promises';
import path from 'node:path';

const projectRoot = process.cwd();
const publicVendorRoot = path.join(projectRoot, 'public', 'vendor', 'dashboard');

const copyTargets = [
  {
    from: path.join(projectRoot, 'node_modules', 'vue', 'dist', 'vue.global.prod.js'),
    to: path.join(publicVendorRoot, 'vue', 'vue.global.prod.js'),
  },
  {
    from: path.join(projectRoot, 'node_modules', 'papaparse', 'papaparse.min.js'),
    to: path.join(publicVendorRoot, 'papaparse', 'papaparse.min.js'),
  },
  {
    from: path.join(projectRoot, 'node_modules', 'apexcharts', 'dist', 'apexcharts.min.js'),
    to: path.join(publicVendorRoot, 'apexcharts', 'apexcharts.min.js'),
  },
  {
    from: path.join(projectRoot, 'node_modules', '@fortawesome', 'fontawesome-free', 'css', 'all.min.css'),
    to: path.join(publicVendorRoot, 'fontawesome', 'css', 'all.min.css'),
  },
];

async function copyFontAwesomeWebfonts() {
  const sourceDir = path.join(projectRoot, 'node_modules', '@fortawesome', 'fontawesome-free', 'webfonts');
  const targetDir = path.join(publicVendorRoot, 'fontawesome', 'webfonts');
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
  await rm(publicVendorRoot, { recursive: true, force: true });

  await Promise.all(
    copyTargets.map(async ({ from, to }) => {
      await mkdir(path.dirname(to), { recursive: true });
      await copyFile(from, to);
    })
  );

  await copyFontAwesomeWebfonts();

  process.stdout.write('Dashboard vendor assets synced.\n');
}

main().catch((error) => {
  process.stderr.write(`${error instanceof Error ? error.stack ?? error.message : String(error)}\n`);
  process.exitCode = 1;
});
