"""
Generate raster SIPKL brand assets (favicon, apple-touch-icon, og-image)
from the same geometry as public/images/sipkl-mark.svg.

Run:  python tools/build_brand_assets.py
"""

import os
from PIL import Image, ImageDraw, ImageFilter, ImageFont

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(ROOT, "public", "images")
PUBLIC = os.path.join(ROOT, "public")

BLUE_LIGHT = (59, 130, 246)
BLUE = (37, 99, 235)
BLUE_DARK = (29, 78, 216)
BAND = (199, 219, 255)
WHITE = (255, 255, 255)
ACCENT = (125, 211, 252)
INK = (11, 28, 48)
MUTED = (67, 70, 85)
SURFACE = (248, 249, 255)

FONT_SB = os.path.join(os.environ.get("WINDIR", r"C:\Windows"), "Fonts", "seguisb.ttf")
FONT_BD = os.path.join(os.environ.get("WINDIR", r"C:\Windows"), "Fonts", "segoeuib.ttf")
FONT_RG = os.path.join(os.environ.get("WINDIR", r"C:\Windows"), "Fonts", "segoeui.ttf")

SS = 8  # supersample factor


def _grad(size, c1, c2, diagonal=True):
    w, h = size
    img = Image.new("RGB", (w, h), c1)
    px = img.load()
    span = (w + h) if diagonal else w
    for y in range(h):
        for x in range(w):
            t = ((x + y) if diagonal else x) / max(1, span - 1)
            px[x, y] = (
                round(c1[0] + (c2[0] - c1[0]) * t),
                round(c1[1] + (c2[1] - c1[1]) * t),
                round(c1[2] + (c2[2] - c1[2]) * t),
            )
    return img


def mark(size):
    """Render the SIPKL mark at `size` px (square, transparent outside)."""
    S = size * SS
    u = S / 64.0  # svg units -> px

    plate = _grad((S, S), BLUE_LIGHT, BLUE_DARK).convert("RGBA")
    mask = Image.new("L", (S, S), 0)
    ImageDraw.Draw(mask).rounded_rectangle([0, 0, S - 1, S - 1], radius=16 * u, fill=255)
    img = Image.new("RGBA", (S, S), (0, 0, 0, 0))
    img.paste(plate, (0, 0), mask)

    d = ImageDraw.Draw(img)

    # cap band (bottom corners rounded)
    x0, x1 = 18.4 * u, 45.6 * u
    d.rounded_rectangle(
        [x0, 27.2 * u, x1, 45.6 * u],
        radius=13.6 * u,
        corners=(False, False, True, True),
        fill=BAND,
    )

    # mortarboard top
    d.polygon(
        [(32 * u, 12.4 * u), (55 * u, 23.5 * u), (32 * u, 34.6 * u), (9 * u, 23.5 * u)],
        fill=WHITE,
    )

    # tassel cord + accent bead
    tw = max(1, int(round(2.4 * u)))
    d.line([(55 * u, 24.5 * u), (55 * u, 33.4 * u)], fill=WHITE, width=tw)
    d.ellipse([55 * u - tw / 2, 24.5 * u - tw / 2, 55 * u + tw / 2, 24.5 * u + tw / 2], fill=WHITE)
    r = 3.3 * u
    d.ellipse([55 * u - r, 36.6 * u - r, 55 * u + r, 36.6 * u + r], fill=ACCENT)

    return img.resize((size, size), Image.LANCZOS)


def og_image(w=1200, h=630):
    img = _grad((w, h), (237, 243, 255), (203, 219, 245)).convert("RGBA")

    # soft ambient blobs (light, no heavy animation)
    blob = Image.new("RGBA", (w, h), (0, 0, 0, 0))
    bd = ImageDraw.Draw(blob)
    bd.ellipse([-220, -260, 620, 380], fill=(37, 99, 235, 42))
    bd.ellipse([700, 300, 1420, 1020], fill=(56, 189, 248, 34))
    blob = blob.filter(ImageFilter.GaussianBlur(90))
    img.alpha_composite(blob)

    m = mark(140)
    img.alpha_composite(m, (80, 92))

    d = ImageDraw.Draw(img)
    f_badge = ImageFont.truetype(FONT_SB, 26)
    f_h1 = ImageFont.truetype(FONT_BD, 74)
    f_h2 = ImageFont.truetype(FONT_RG, 34)
    f_p = ImageFont.truetype(FONT_RG, 28)

    badge = "PLATFORM PKL TERINTEGRASI"
    badge_w = round(d.textlength(badge, font=f_badge)) + 52
    d.rounded_rectangle([248, 118, 248 + badge_w, 118 + 48], radius=24, fill=(255, 255, 255, 220))
    d.text((248 + 26, 118 + 11), badge, font=f_badge, fill=BLUE_DARK)

    d.text((80, 274), "SIPKL", font=f_h1, fill=BLUE_DARK)
    d.text(
        (80, 368),
        "Sistem Informasi Praktik Kerja Lapangan",
        font=f_h2,
        fill=INK,
    )
    d.text(
        (80, 452),
        "Pengajuan  \u2022  Verifikasi  \u2022  Bimbingan  \u2022  Laporan  \u2022  Penilaian",
        font=f_p,
        fill=MUTED,
    )

    d.rectangle([0, h - 12, w, h], fill=BLUE_DARK)
    return img.convert("RGB")


def _save_png(img, path, colors=192):
    """Simpan PNG dengan palet terkuantisasi supaya file tetap ringan."""
    q = img.convert("RGBA")
    alpha = q.getchannel("A")
    rgb = Image.merge("RGB", q.split()[:3]).quantize(colors=colors, method=Image.MEDIANCUT, dither=0)
    out = rgb.convert("RGBA")
    out.putalpha(alpha)
    out.save(path, optimize=True)


def main():
    os.makedirs(OUT, exist_ok=True)

    _save_png(mark(512), os.path.join(OUT, "sipkl-mark.png"))
    _save_png(mark(128), os.path.join(PUBLIC, "favicon.png"))

    mark(512).save(
        os.path.join(PUBLIC, "favicon.ico"),
        sizes=[(16, 16), (24, 24), (32, 32), (48, 48), (64, 64), (128, 128), (256, 256)],
    )

    _save_png(mark(180), os.path.join(OUT, "apple-touch-icon.png"))
    og_image().save(os.path.join(OUT, "og-image.jpg"), quality=88, optimize=True, progressive=True)

    for f in ("sipkl-mark.png", "apple-touch-icon.png", "og-image.jpg"):
        p = os.path.join(OUT, f)
        print(f, os.path.getsize(p), "bytes")
    for f in ("favicon.png", "favicon.ico"):
        p = os.path.join(PUBLIC, f)
        print(f, os.path.getsize(p), "bytes")


if __name__ == "__main__":
    main()
