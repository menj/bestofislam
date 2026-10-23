#!/usr/bin/env python3
"""Generate original geometric graphics (site palette) for articles with no suitable PD photograph.

Usage: bin/make-original-graphics.py <slug> <inline-count>
Writes assets/images/featured/<slug>.avif (1400x934) and assets/images/inline/<slug>-N.avif (960x768).
The design is derived from a hash of the slug, so a rerun gives the same result.
"""
import hashlib, math, sys
from pathlib import Path
from PIL import Image, ImageDraw

NAVY, GOLD, BLUE, PALE = (13, 43, 78), (232, 179, 60), (63, 169, 245), (240, 244, 250)
ROOT = Path(__file__).resolve().parent.parent

def draw(slug, idx, w, h):
    seed = int(hashlib.sha256(f"{slug}-{idx}".encode()).hexdigest(), 16)
    rnd = lambda n: (seed >> (n * 7)) % 1000 / 1000
    S = 3
    im = Image.new("RGB", (w * S, h * S), NAVY)
    d = ImageDraw.Draw(im)
    cx, cy = w * S * (0.35 + 0.3 * rnd(1)), h * S * (0.4 + 0.2 * rnd(2))
    n = 8 + int(rnd(3) * 5) * 2          # points of the star
    rings = 5 + int(rnd(4) * 4)
    base = min(w, h) * S * (0.085 + 0.02 * rnd(5))
    for r in range(rings, 0, -1):
        rad = base * r * 0.78
        col = GOLD if r % 3 == 0 else (BLUE if r % 3 == 1 else PALE)
        width = max(2, int(S * (1.2 if r % 3 else 2.4)))
        pts = []
        for k in range(n * 2):
            a = math.pi * k / n + rnd(6) * 0.4
            rr = rad if k % 2 == 0 else rad * (0.62 + 0.1 * rnd(7))
            pts.append((cx + rr * math.cos(a), cy + rr * math.sin(a)))
        d.line(pts + [pts[0]], fill=col, width=width, joint="curve")
    d.ellipse([cx - base * 0.25, cy - base * 0.25, cx + base * 0.25, cy + base * 0.25], fill=GOLD)
    return im.resize((w, h), Image.LANCZOS)

def main():
    slug, inline = sys.argv[1], int(sys.argv[2])
    out = ROOT / "assets" / "images"
    (out / "featured").mkdir(parents=True, exist_ok=True)
    (out / "inline").mkdir(parents=True, exist_ok=True)
    draw(slug, 0, 1400, 934).save(out / "featured" / f"{slug}.avif", quality=60)
    for i in range(1, inline + 1):
        draw(slug, i, 960, 768).save(out / "inline" / f"{slug}-{i}.avif", quality=60)

if __name__ == "__main__":
    main()
