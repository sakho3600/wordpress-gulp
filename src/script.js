'use strict';

// Open external links in new tab
function externalLinks() {
  for (var c = document.getElementsByTagName('a'), a = 0; a < c.length; a++) {
    var b = c[a];
    b.getAttribute('href') && (b.hostname !== location.hostname && /^(f|ht)tps?:\/\//i.test(b.href)) && (b.target = '_blank');
  }
}
externalLinks();
