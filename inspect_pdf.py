import io
import sys

import pymupdf

names = sys.argv[1:] or ['paye', 'payepromo', 'gratuit']
out = io.StringIO()


def isvio(i):
    r, g, b = i[0], i[1], i[2]
    return 70 <= r <= 100 and 20 <= g <= 55 and 110 <= b <= 145


for name in names:
    doc = pymupdf.open(r'C:\Projets\PassEvent\storage\app\ticket_' + name + '.pdf')
    out.write('=== %s pages=%d page_h=%.2f\n' % (name, len(doc), doc[0].rect.height))
    for pi in range(len(doc)):
        page = doc[pi]
        for b in page.get_text('dict')['blocks']:
            if b['type'] != 0:
                continue
            for l in b['lines']:
                s = ''.join(sp['text'] for sp in l['spans'])
                if s.strip():
                    y0, y1 = l['bbox'][1], l['bbox'][3]
                    out.write('p%d y0=%6.1f y1=%6.1f %r\n' % (pi, y0, y1, s[:60]))
        pix = page.get_pixmap(dpi=120)
        w, h = pix.width, pix.height
        rows = []
        for y in range(h):
            cnt = 0
            for x in range(0, w, 2):
                if isvio(pix.pixel(x, y)):
                    cnt += 1
            if cnt > 3:
                rows.append((y, cnt))
        groups = []
        for y, c in rows:
            if groups and y - groups[-1][-1][0] <= 3:
                groups[-1].append((y, c))
            else:
                groups.append([(y, c)])
        for g in groups:
            ys = [r[0] for r in g]
            out.write('  p%d violet band rows %d->%d pt %.1f->%.1f\n' % (pi, ys[0], ys[-1], ys[0] * 72 / 120, ys[-1] * 72 / 120))
sys.stdout.write(out.getvalue())
