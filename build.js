const fs = require('fs');
const path = require('path');

const assets = [
	'@google/markerclustererplus/dist/markerclustererplus.min.js',
	'@google/markerclustererplus/dist/markerclustererplus.min.js.map',
	'@google/markerclustererplus/images/m1.png',
	'@google/markerclustererplus/images/m2.png',
	'@google/markerclustererplus/images/m3.png',
	'@google/markerclustererplus/images/m4.png',
	'@google/markerclustererplus/images/m5.png',
	'npm-overlapping-marker-spiderfier/lib/oms.min.js',
	'geocomplete/jquery.geocomplete.min.js',
];

assets.forEach((asset) => {
	const src = path.join('node_modules', asset);
	const dest = path.join('assets/vendor', asset);
	const isDir = fs.statSync(src).isDirectory();
	const destDir = isDir ? dest : path.dirname(dest);

	if (!fs.existsSync(destDir)) {
		fs.mkdirSync(destDir, { recursive: true });
	}

	if (isDir) {
		fs.cpSync(src, dest, { recursive: true });
	} else {
		fs.copyFileSync(src, dest);
	}
});
