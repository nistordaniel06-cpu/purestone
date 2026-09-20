import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const htmlPath = path.join(root, 'index.html');
const mapPath = path.join(root, 'data', 'product-image-map.json');

const map = JSON.parse(fs.readFileSync(mapPath, 'utf8'));
let html = fs.readFileSync(htmlPath, 'utf8');

const replacements = [];

for (const item of map.materials) {
  if (item.sourceTexture && item.targetTexture) {
    replacements.push([item.sourceTexture, item.targetTexture]);
  }
  if (item.sourceLifestyle && item.targetLifestyle) {
    replacements.push([item.sourceLifestyle, item.targetLifestyle]);
  }
}

for (const item of map.portfolio) {
  if (item.source && item.target) {
    replacements.push([item.source, item.target]);
  }
}

const unique = new Map(replacements);
let changed = 0;
let skipped = 0;

for (const [sourceUrl, targetRel] of unique.entries()) {
  const targetAbs = path.join(root, targetRel);
  if (!fs.existsSync(targetAbs)) {
    console.log(`SKIP  ${targetRel} (file not found)`);
    skipped++;
    continue;
  }

  if (!html.includes(sourceUrl)) {
    console.log(`MISS  ${sourceUrl}`);
    continue;
  }

  html = html.split(sourceUrl).join(targetRel);
  console.log(`OK    ${targetRel}`);
  changed++;
}

if (changed > 0) {
  fs.writeFileSync(htmlPath, html, 'utf8');
}

console.log(`\nDone. Replaced: ${changed}. Skipped missing assets: ${skipped}.`);
console.log('The script never swaps a product to another product: each replacement is keyed by its original source URL.');
