# Script fonts

`assets/css/scripts.css` declares six range-scoped faces. Noto Naskh Arabic is
bundled (`noto-naskh-arabic.woff2`, a variable file for weights 400 to 700,
under the SIL Open Font Licence 1.1, which allows redistribution with the
theme; its copyright notice and the licence text are in
`OFL-NotoNaskhArabic.txt`); it sets the Arabic of the Glossary and every other Arabic passage that
is not Quranic. The other files are not bundled, since their licences require
the site owner to obtain them. Drop each woff2 into this directory under the exact filename below
and the declaration activates with no code change.

| Filename | Face | Scripts covered |
| --- | --- | --- |
| `uthmanic-hafs.woff2` | KFGQPC HAFS Uthmanic | Quranic Arabic |
| `noto-naskh-arabic.woff2` (bundled) | Noto Naskh Arabic | General Arabic |
| `sbl-hebrew.woff2` | SBL Hebrew | Hebrew, Hebrew Presentation Forms |
| `sbl-greek.woff2` | SBL Greek | Greek, Greek Extended |
| `evangelion-cpa.woff2` | Evangelion CPA | Syriac, Syriac Supplement |
| `noto-sans-cuneiform.woff2` | Noto Sans Cuneiform | Akkadian |

The same six faces were converted for the bestofislam.com theme, so the
converted files may be copied across directly.

## Conversion

```
pip install fonttools brotli
fonttools ttLib.woff2 compress -o uthmanic-hafs.woff2 UthmanicHafs.ttf
```

## Usage in content

Wrap a citation in one of the helper classes so direction and sizing resolve
correctly:

- `.boi-script-quran` for Quranic Arabic, right to left, enlarged
- `.boi-script-arabic`, `.boi-script-hebrew`, `.boi-script-syriac` for other
  right-to-left passages
- `.boi-script-greek` for Greek

Unwrapped text still renders in the correct face, since the stack is applied to
`body` and scoped by `unicode-range`. The classes govern direction and scale.
