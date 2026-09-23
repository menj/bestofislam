# Script fonts

`assets/css/scripts.css` declares six range-scoped faces. The files are not
bundled with this release, since their licences require the site owner to
obtain them. Drop each woff2 into this directory under the exact filename below
and the declaration activates with no code change.

| Filename | Face | Scripts covered |
| --- | --- | --- |
| `uthmanic-hafs.woff2` | KFGQPC HAFS Uthmanic | Quranic Arabic |
| `noto-naskh-arabic.woff2` | Noto Naskh Arabic | General Arabic |
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
