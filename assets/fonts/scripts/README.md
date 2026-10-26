# Script fonts

`assets/css/scripts.css` declares six range-scoped faces. Two are bundled:

- `noto-naskh-arabic.woff2`, a variable file for weights 400 to 700, under the
  SIL Open Font Licence 1.1 (`OFL-NotoNaskhArabic.txt`); it sets the Arabic of
  the Glossary and every other Arabic passage that is not Quranic.
- `kfgqpc-hafs-uthmanic-script.woff2`, KFGQPC HAFS Uthmanic Script (version 2.2)
  by the King Fahd Glorious Quran Printing Complex, under the free no-cost
  end-user licence embedded in the font and reproduced in
  `kfgqpc-hafs-uthmanic-script-LICENSE.txt` (use, copy and distribute are
  permitted; the font may not be sold, modified, altered or reproduced in
  another form). The file is a woff2 conversion of the original TTF, not
  subset: the owner chose on 2026-10-26 to serve it so (107 KB, from 298 KB),
  accepting that the licence forbids modifying the font. The conversion is
  lossless for glyphs, cmap and OpenType layout; the digital signature (DSIG)
  is dropped. It sets only
  Quranic verses: text in the `.boi-script-quran` class
  (`.boi-term__script.boi-script-quran` on a Glossary entry) and
  `.boi-glossary__ar--quran` in the Glossary index, both reserved for entries
  with a `quran_ref`, through the `--boi-font-quran` token. It is not in the
  `body` stack, so unmarked Arabic never resolves to it. The same file ships in
  all seven themes of the network.

The remaining four files are not bundled, since their licences require the
site owner to obtain them. Drop each woff2 into this directory under the
exact filename below and the declaration activates with no code change.

| Filename | Face | Scripts covered |
| --- | --- | --- |
| `kfgqpc-hafs-uthmanic-script.woff2` (bundled) | KFGQPC HAFS Uthmanic Script | Quranic verses only |
| `noto-naskh-arabic.woff2` (bundled) | Noto Naskh Arabic | General Arabic |
| `sbl-hebrew.woff2` | SBL Hebrew | Hebrew, Hebrew Presentation Forms |
| `sbl-greek.woff2` | SBL Greek | Greek, Greek Extended |
| `evangelion-cpa.woff2` | Evangelion CPA | Syriac, Syriac Supplement |
| `noto-sans-cuneiform.woff2` | Noto Sans Cuneiform | Akkadian |

## Conversion

The four unbundled faces, where their licences allow it, may be converted to
woff2. The KFGQPC file is already a woff2; do not subset it.

```
pip install fonttools brotli
fonttools ttLib.woff2 compress -o sbl-hebrew.woff2 SBL_Hbrw.ttf
```

## Usage in content

Wrap a citation in one of the helper classes so direction and sizing resolve
correctly:

- `.boi-script-quran` for Quranic verses, right to left, enlarged, in the
  Uthmanic face. To number a verse, type the Arabic-Indic digits alone (٢٥):
  the font draws them inside the ayah circle itself. Do not add U+06DD END OF
  AYAH or a CSS ornament as well, or the number shows two circles.
- `.boi-script-arabic`, `.boi-script-hebrew`, `.boi-script-syriac` for other
  right-to-left passages
- `.boi-script-greek` for Greek

Unwrapped text still renders in the correct face, since the stack is applied to
`body` and scoped by `unicode-range`. Unwrapped Arabic, a verse included, sets
in Noto Naskh Arabic; only the Quran class gives the Uthmanic face.
