const fs = require('fs');
const {readFile, writeFile} = require('fs');
let rawdata = fs.readFileSync('./public/build-web/manifest.json');

let manifest = JSON.parse(rawdata);

let webcss = './public/build-web/'+manifest['resources/sass/web.scss']['file'];
readFile(webcss, 'utf-8', function (err, contents) {
  if (err) {
    console.log(err);
    return;
  }

  const replaced = contents.replaceAll('/build/', '/build-web/');

  writeFile(webcss, replaced, 'utf-8', function (err) {
    console.log(err);
    console.log('berhasil direplace')
  });
});
