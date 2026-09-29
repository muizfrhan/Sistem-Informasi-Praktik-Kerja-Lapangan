"""
Optimasi public/template/hero.jpg untuk dipakai sebagai background hero.

Sumber: 7952x5304 (rasio 1.499, 821 KB) — terlalu besar & terlalu tinggi untuk web,
         dan rasionya tidak cocok dengan section hero yangragian landscape.

Target:
  - lebar 1920 px (cukup untuk 2x di layar 960 CSS px, masih tajam di 1440/1920 desktop)
  - crop rasio 1.6 supaya section yang sangat lebar tidak memotong terlalu banyak
  - focal point condong ke kiri-atas: subjek (siswa) ada di kiri-tengah
  - jpg (kualitas 82) untuk fallback + webp (kualitas 78) sebagai <source>
"""
from PIL import Image, ImageFilter
from pathlib import Path

SRC = Path("public/template/hero.jpg")
OUT_JPG = Path("public/images/hero-bg.jpg")
OUT_WEBP = Path("public/images/hero-bg.webp")

TARGET_W = 1920
TARGET_RATIO = 1.6  # lebar / tinggi

img = Image.open(SRC)
img = img.convert("RGB")
print(f"source: {img.width}x{img.height} ratio={img.width / img.height:.3f}")

# 1) Resize ke lebar target, tinggi mengikuti rasio sumber.
scale = TARGET_W / img.width
resized = img.resize((TARGET_W, round(img.height * scale)), Image.LANCZOS)
print(f"resized: {resized.width}x{resized.height} ratio={resized.width / resized.height:.3f}")

# 2) Crop vertikal ke rasio target bila masih terlalu "tinggi".
want_h = round(TARGET_W / TARGET_RATIO)
if resized.height > want_h:
    # Fokal point: subjek utama (wajah + meja kerja) berada di sekitar 45% tinggi.
    # Ambil slightly di atas tengah agar wajah tidak jatuh terlalu bawah.
    center = round(resized.height * 0.46)
    top = max(0, min(resized.height - want_h, center - want_h // 2))
    box = (0, top, TARGET_W, top + want_h)
    final = resized.crop(box)
    print(f"cropped to ratio {TARGET_RATIO}: {final.width}x{final.height} (top={top})")
else:
    # Src lebih landai dari target -> crop kiri-kanan, fokal ke kiri (subjek ada di kiri).
    want_w = round(resized.height * TARGET_RATIO)
    center = round(resized.width * 0.42)
    left = max(0, min(resized.width - want_w, center - want_w // 2))
    final = resized.crop((left, 0, left + want_w, resized.height))
    print(f"cropped to ratio {TARGET_RATIO}: {final.width}x{final.height} (left={left})")

# 3) Slight unsharp mask supaya tetap tajam setelah resize besar.
final = final.filter(ImageFilter.UnsharpMask(radius=1.2, percent=55, threshold=3))

final.save(OUT_JPG, "JPEG", quality=82, optimize=True, progressive=True)
final.save(OUT_WEBP, "WEBP", quality=78, method=6)

for p in (OUT_JPG, OUT_WEBP):
    print(f"{p}: {p.stat().st_size / 1024:.1f} KB  ({final.width}x{final.height})")