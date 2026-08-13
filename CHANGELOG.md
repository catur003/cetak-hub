# CHANGELOG - RepoFlow Mobile

## 13 Agustus 2026 (batch 16 - split Ad Unit ID string + nyalain R8)

### Added
- **Split-string Ad Unit ID** (`src/lib/ads.js`) - `PROD_INTERSTITIAL_ID`
  & `PROD_BANNER_ID` sekarang `[prefix, angka].join('/')`, bukan string
  literal utuh. Bukan enkripsi (orang niat serius tetap bisa nyambungin
  manual) - tujuannya nggagalin pencarian teks polos "ca-app-pub-..." di
  bundle JS yang udah di-minify (minifier Metro/Terser tidak menyentuh
  isi string literal, cuma nama variabel). TestIds Google TIDAK
  di-split (memang publik, bukan sesuatu yang perlu disembunyikan).
- **R8 (ProGuard) + resource shrinking dinyalain** buat release Android
  (`app.json` - plugin `expo-build-properties`,
  `enableProguardInReleaseBuilds` + `enableShrinkResourcesInReleaseBuilds`
  jadi `true`, sebelumnya OFF/default). Cuma nyentuh kode native
  Java/Kotlin (Expo modules + SDK Ads) - AAB/APK jadi lebih kecil dan
  kode native lebih susah dibaca-balik. TIDAK menyentuh bundle JS (beda
  layer dari split-string di atas).

### Catatan build
- WAJIB `npx expo prebuild --clean` (plugin `app.json` berubah).
- R8 kadang butuh keep-rule tambahan buat library tertentu kalau ada
  fitur yang tiba-tiba error/crash setelah dinyalain (biasanya
  react-native-google-mobile-ads udah nyertain consumer-rules sendiri,
  tapi tetap worth di-smoke-test sekali sebelum submit ke Play Store).

### Files changed (batch 16)
- `app.json`
- `src/lib/ads.js`

---

## 13 Agustus 2026 (batch 15 - fix duplicate key warning CompareScreen)

### Fixed
- **"Encountered two children with the same key" nongol di semua tab**
  (laporan Zen, screenshot LogBox). Root cause: `CompareScreen.js` -
  `keyExtractor` daftar file diff pakai `f.sha || f.filename`. `sha` di
  situ itu hash ISI file (content-addressed by git) - dua file yang
  isinya identik (mis. dua file kosong, dua boilerplate yang sama
  persis) punya `sha` SAMA walau path-nya beda, bikin React nemu dua
  item dengan key kembar. Fix: prioritas dibalik ke `f.filename` (path,
  yang beneran unik per baris diff) - `f.sha` cuma fallback kalau
  filename entah kenapa gak ada. LogBox error ini sifatnya overlay
  global makanya kelihatan "di semua tab" walau sumbernya cuma satu
  layar (CompareScreen).

### Files changed (batch 15)
- `src/screens/CompareScreen.js`

---

## 13 Agustus 2026 (batch 14 - navbar satu baris, fix keyboard nutup commit box >70 file, fix ignoreRules)

### Fixed
- **Navbar banner "ga konsisten"** (revisi dari batch 13). Dulu: banner
  nambah baris kedua di bawah pill + pill mengecil jadi varian
  `compact`. Sekarang: SATU baris - pill & `AdBanner` gantian di slot
  yang sama (`display:'none'` pada pill begitu iklan `loaded`, bukan
  mengecil). Pill & avatar sekarang cuma SATU ukuran tetap, gak ada lagi
  varian compact/normal yang gonta-ganti (App.js - `AppTopBar`,
  `topStyles`).
- **Commit box ketutup keyboard kalau file > 70** (laporan Zen "30-50
  aman, 70+ ketutup"). Akar masalah: `FlatList` tanpa `getItemLayout`
  cuma NEBAK tinggi total konten dari row yang kebetulan sudah
  ke-render (virtualization) - tebakan makin meleset makin banyak row
  yang belum pernah dirender, bikin `scrollToEnd()` berhenti sebelum
  beneran mentok bawah. Fix: `getItemLayout` (offset di-precompute lewat
  `useMemo`, O(1) per lookup) - FlatList dikasih tau persis posisi tiap
  row secara matematis, gak perlu nebak lagi berapa pun jumlah filenya
  (`UploadScreen.js`).
- **Tombol "Sertakan semua" kelihatan gak berfungsi**. Akar masalah:
  `ignoredCount` ngitung SEMUA file berflag `ignored`, gak peduli udah
  dicentang atau belum - abis "Sertakan semua" ditekan, angka & bar-nya
  tetap sama persis (padahal file-nya SUDAH ke-include), user ngira
  gagal. Sekarang `ignoredCount` cuma ngitung yang MASIH belum
  dicentang - begitu semua di-include, bar-nya (`ignoredCount > 0`)
  otomatis hilang. `includeAllIgnored()` juga sekalian buka daftar
  (`setShowIgnored(true)`) biar user langsung lihat efeknya di list.
- **`vendor/` dihapus dari daftar auto-ignore & template `.gitignore`
  bawaan** (`ignoreRules.js`) - folder ini legit dipakai project
  PHP/Composer (Laravel dkk), beda kasus dari `node_modules` yang emang
  generatable ulang.

### Added
- **Popup jelas kalau ada file yang dilewati otomatis** (permintaan Zen
  "kasih notif yg jelas, popup gitu"). Dulu cuma bar teks kecil di
  tengah daftar (gampang kelewat). Sekarang `appAlert` muncul begitu ada
  file yang match pola ignore - baik dari pilih file langsung maupun
  dari konfirmasi preview zip (`notifySkipped()`, dipanggil dari dua
  titik masuk file di `UploadScreen.js`).

### Files changed (batch 14)
- `App.js`
- `src/lib/ignoreRules.js`
- `src/screens/UploadScreen.js`

---

## 13 Agustus 2026 (batch 13 - badge PR siap merge, banner navbar, interstitial 4 jam)

### Added
- **Badge PR siap merge** (tombol "PR" di toolbar BrowserScreen). PR
  dihitung "siap" kalau: open, bukan draft, dan check gabungan
  (`combineCheckState`, sekarang di `src/lib/checkState.js` - dipindah
  dari PullRequestsModal.js biar RepoContext.js bisa pakai juga tanpa
  circular import) statusnya `success` ATAU `none` (repo tanpa CI sama
  sekali, gak ada yang perlu ditunggu). Sengaja TIDAK cek field GitHub
  `mergeable` (soal conflict) - field itu cuma ada di endpoint PR
  tunggal (bukan list) dan sering `null` selagi GitHub masih ngitung di
  background, andalin itu bikin badge kedip-kedip. Badge ini sinyal
  "checks lolos semua", keputusan akhir tetap di tombol Merge asli.
  - `RepoContext.js`: state `mergeReadyCount` + fungsi
    `refreshMergeReadyCount(token)`, reset ke `null` tiap owner/repo
    aktif ganti, ada guard race (`mergeReadyReqIdRef`) biar fetch lama
    yang telat balik gak nimpa hasil fetch yang lebih baru.
  - `BrowserScreen.js`: fetch pas repo aktif ganti / tab Files jadi
    aktif lagi, poll tiap 4 menit SELAMA tab ini aktif (interval
    dibersihkan begitu pindah tab), plus refresh instan begitu modal PR
    ditutup (siapa tau ada yang baru di-merge di dalamnya).
  - `UI.js` (`IconButton`): prop baru `badge` - lingkaran kecil pojok
    kanan-atas, `undefined`/`0`/`null` = gak render apa-apa, angka > 9
    ditampilin "9+".

- **Banner ad pindah ke navbar, tampil di SEMUA tab** (sebelumnya cuma
  di UploadScreen pas kosong). `AppTopBar` (App.js) dirender sekali di
  luar switch tab, jadi banner otomatis kelihatan di tab manapun tanpa
  diulang di tiap screen.
  - `AdBanner.js`: state 3-fase (`loading`/`loaded`/`failed`, dulu cuma
    boolean `failed`) + wrapper collapse (`height: 0, overflow: hidden`)
    selama belum ada kepastian - gak ada kotak iklan blank nyempil pas
    masih loading. Callback baru `onLoaded`/`onFailed` buat parent tau
    state-nya.
  - `AppTopBar`: pill repo/branch + avatar otomatis "compact" (font &
    padding lebih kecil) begitu banner LOADED, balik ukuran normal
    kalau banner gagal load - defaultnya optimis gede duluan, baru
    mengecil pas banner beneran nongol (bukan sebaliknya, biar gak ada
    pill yang sempat kekecilan lalu balik gede lagi kalau ternyata
    gagal).
  - `UploadScreen.js`: banner versi lama (`files.length === 0`) dihapus,
    gak dobel sama yang di navbar.

- **Interstitial: cooldown 4 jam, persisten lintas cold-start**
  (sebelumnya "sekali per cold start" doang, cocok buat tahap tes awal).
  `useOpenAppInterstitial.js`: timestamp show terakhir disimpan
  AsyncStorage (`zen.ads.interstitial_last_shown_at`), dicek SEBELUM
  load - interstitial cuma di-load+show kalau udah lewat >= 4 jam dari
  timestamp itu. Cooldown TIDAK direset kalau load gagal (cuma ditulis
  pas beneran `show()` sukses) - percobaan berikutnya tetap ngitung dari
  show terakhir yang sukses, bukan dari percobaan gagal. "Jangan kasih
  timer" (instruksi awal Zen) masih dihormati di titik show() itu
  sendiri - gak ada delay artifisial begitu ad-nya lolos cooldown &
  selesai load.

### Files changed (batch 13)
- `App.js`
- `src/components/AdBanner.js`
- `src/components/PullRequestsModal.js`
- `src/components/UI.js`
- `src/context/RepoContext.js`
- `src/hooks/useOpenAppInterstitial.js`
- `src/lib/checkState.js` (baru)
- `src/screens/BrowserScreen.js`
- `src/screens/UploadScreen.js`

---

## 12 Agustus 2026 (batch 12 - fix build AdMob: Kotlin metadata mismatch)

Fix build error `:react-native-google-mobile-ads:compileDebugKotlin FAILED`
(`Module was compiled with an incompatible version of Kotlin. The binary
version of its metadata is 2.3.0, expected version is 2.1.0.`).

### Root cause
`play-services-ads-25.4.0` (native SDK Google Ads, ditarik otomatis oleh
`react-native-google-mobile-ads@16.4.0`) di-compile vendor pakai Kotlin
2.3.0. Expo SDK 54 defaultnya masih Kotlin 2.1.20 - compiler versi lama
nolak baca metadata dari versi yang lebih baru. `react-native-google-mobile-ads`
belum sediain cara resmi override versi `play-services-ads` lewat
app.json, dan naikin `kotlinVersion` project lewat `expo-build-properties`
ke 2.3.x dilaporkan gak selalu diterima mulus di SDK 54 (issue publik).

### Added
- `plugins/withKotlinMetadataSkip.js` (custom Expo config plugin baru) -
  nyisipin `-Xskip-metadata-version-check` ke semua subproject Kotlin
  lewat `android/build.gradle` pas prebuild. Ini cuma skip VALIDASI
  VERSI metadata, bukan ganti Kotlin compiler project ke 2.3 - resiko
  cuma muncul kalau dependency-nya makai syntax Kotlin baru yang gak ada
  di compiler lama (gak terjadi buat play-services-ads).
- `app.json`: plugin baru didaftarkan sebelum plugin
  `react-native-google-mobile-ads`.

### Removed
- Plugin `expo-build-properties` (kotlinVersion override) di `app.json` -
  diganti pendekatan `withKotlinMetadataSkip` yang lebih target dan gak
  berisiko konflik sama versi Kotlin bawaan SDK 54.

### Catatan build
- Tetap WAJIB `npx expo prebuild --clean` setelah pull batch ini (plugin
  baru, `android/` perlu digenerate ulang).
- Kalau muncul error `Cannot find module '@expo/config-plugins'` pas
  prebuild: `npm install @expo/config-plugins --save-dev` (biasanya udah
  ke-bundle transitively lewat paket `expo`, tapi kalau resolver lu gak
  hoist itu, install manual aman).

### Files changed (batch 12)
- `app.json`
- `plugins/withKotlinMetadataSkip.js` (baru)

---

## 12 Agustus 2026 (batch 11 - AdMob, tahap tes)

Integrasi awal AdMob (react-native-google-mobile-ads). Semua Ad Unit ID
masih TestIds resmi Google selama `__DEV__` (lihat `src/lib/ads.js`) -
belum pakai ID asli, sengaja karena masih tahap tes.

### Added
- **Interstitial pas app baru dibuka.** `useOpenAppInterstitial` (dipasang
  di `AppShell`, App.js) - nyala begitu `status === 'signedIn'`, TIDAK
  pernah nyala di LoginScreen (`status` masih `'loading'`/`'signedOut'`).
  Tidak ada delay/timer sebelum show() (masih tahap tes, sesuai
  instruksi) - langsung tampil begitu iklan selesai di-load. Flag
  module-level (`shownThisSession`) memastikan cuma tampil sekali per
  cold start, bukan tiap kali AppShell re-render.
- **Banner di halaman kosong.** `AdBanner` (komponen baru, reusable) -
  dipasang pertama kali di UploadScreen, di bawah tombol "Pilih Zip /
  File (Ekstrak)". Hanya render selagi `files.length === 0`; begitu user
  mulai pilih file, banner hilang duluan (tidak ganggu daftar file/alur
  commit).
- `app.json`: plugin config `react-native-google-mobile-ads` ditambahkan
  (androidAppId/iosAppId masih App ID contoh/test resmi Google).

### Catatan build (PENTING)
- Ini nambah native module baru - PERLU `npx expo prebuild --clean` lalu
  build ulang (EAS atau `expo run:android`). Upload lewat zip/GitHub SAJA
  TIDAK CUKUP untuk perubahan ini, sama seperti pola SSH client di
  ZenVPSApp.
- Ganti `PROD_INTERSTITIAL_ID` / `PROD_BANNER_ID` di `src/lib/ads.js` ke
  Ad Unit ID asli dari akun AdMob sebelum rilis - JANGAN pakai ID asli
  selama masih testing di device sendiri (risiko invalid traffic /
  suspend akun AdMob).

### Files changed (batch 11)
- `App.js`
- `app.json`
- `package.json`
- `src/lib/ads.js` (baru)
- `src/hooks/useOpenAppInterstitial.js` (baru)
- `src/components/AdBanner.js` (baru)
- `src/screens/UploadScreen.js`

---

## 11 Agustus 2026 (batch 10 - fix arsitektural: memory yang hilang tiap navigasi)

Batch ini beda dari batch-batch sebelumnya - bukan nambal gejala baru,
tapi benerin POLA yang bikin bug versi sebelumnya (walau sudah dipatch)
tetap muncul lagi di tempat lain. Root cause umum: semua "optimistic
update" sebelumnya nyimpen di state/ref yang HILANG begitu user pindah
branch atau modal-nya ditutup-buka lagi - jadi begitu "ingatan" itu
hilang, app balik nanya ke GitHub API mentah-mentah, dan kalau
kebetulan GitHub-nya masih lag, bug yang sama muncul lagi di momen lain.

### Fixed
- **Notif "Buat PR" balik muncul abis pindah branch terus balik lagi
  (bukan cuma abis refresh).** `existingPR` sebelumnya di-null-kan setiap
  branch berganti (memang harus, itu properti per-branch) - tapi begitu
  BALIK ke branch yang PR-nya udah dibuat, app "lupa" fakta itu dan nanya
  ulang ke GitHub dari nol. Ditambahkan `knownPRBranchesRef` - Set
  persistent (hidup selama app jalan, bukan sekadar selama branch itu
  aktif) yang mencatat branch mana aja yang KETAHUAN sudah punya PR open.
  Begitu balik ke branch itu, dicek dari cache ini dulu - tidak perlu
  nanya GitHub ulang sama sekali.
- **List PR abis merge kadang lama ilang (khususnya abis tutup-buka
  modal PR lagi).** Root cause: penanda "PR ini baru saja di-merge dari
  app ini" (`recentlyMergedIds`) sebelumnya `useRef` DI DALAM komponen
  modal - reset ke kosong tiap modal ditutup lalu dibuka lagi. Dipindah
  ke scope modul (`recentlyMergedIdsGlobal`), bertahan selama app-nya
  masih jalan, bukan tergantung modal ini di-mount berapa kali.

### Files changed (batch 10)
- `src/context/RepoContext.js`
- `src/components/PullRequestsModal.js`

---

## 11 Agustus 2026 (batch 9 - branch dropdown kosong total abis pindah branch)

### Fixed
- **Branch master ilang / dropdown branch kosong total (bukan cuma
  ketutup scroll seperti fix sebelumnya - kali ini beneran kosong).**
  Root cause baru: satu `useEffect` di `RepoContext.js` nge-reset
  `branches` ke `[]` setiap kali `active.branch` ganti (harusnya cuma
  reset kalau REPO ganti - daftar branch adalah properti repo, bukan
  properti branch yang lagi dipilih). Fetch ulangnya (`loadBranchesLocal`
  di `RepoPicker.js`) cuma ke-trigger kalau owner/repo ganti, bukan
  branch - jadi begitu user pindah/bikin branch baru dalam repo yang
  sama, list-nya kekosongan di situ dan TIDAK PERNAH terisi lagi sampai
  ganti repo. Dipecah jadi 2 effect terpisah: compare/existingPR/floor
  (reset tiap branch ganti, itu memang benar) vs branches (reset cuma
  pas owner/repo ganti, sinkron sama kapan fetch ulangnya jalan).

### Files changed (batch 9)
- `src/context/RepoContext.js`

---

## 11 Agustus 2026 (batch 8 - audit workflow penuh abis merge)

### Fixed
Audit menyeluruh alur "klik Merge -> Selesai", bukan cuma banner
ahead/behind:
- **Base branch (tab Files) belum nampilin file hasil merge.** Beda dari
  ahead/behind (angka yang app sendiri bisa hitung), ISI file hasil
  merge cuma GitHub yang tahu - jadi tetap perlu fetch ulang, tapi
  sekarang OTOMATIS & DIAM-DIAM: `notifyBranchChanged()` dipanggil abis
  merge sukses, `BrowserScreen`/`UploadScreen` manapun yang lagi
  menampilkan base branch itu auto-retry fetch di background (0s/2s/4s/
  8s) tanpa nge-block UI dengan spinner - sebelumnya user harus sadar
  sendiri & pull-to-refresh manual atau ganti tab bolak-balik.
- **existingPaths (deteksi "akan menimpa file") tidak update abis
  commit sendiri.** Tab Upload tidak pernah refresh existingPaths/
  folders sendiri abis commit sukses di situ - sekarang ditambahkan
  optimistic langsung (app tahu pasti file apa yang baru di-push).

### Files changed (batch 8)
- `src/context/RepoContext.js`
- `src/components/PullRequestsModal.js`
- `src/screens/BrowserScreen.js`
- `src/screens/UploadScreen.js`

---

## 11 Agustus 2026 (batch 7 - optimistic abis merge)

### Fixed
- **List PR abis merge delay.** Sama root cause dengan ahead/behind abis
  push - GitHub `/compare` butuh waktu buat "sadar" branch itu sudah
  ke-merge (bukan soal app nunggu clone/hitung commit, app ini emang
  gapunya clone lokal sama sekali). PR list sendiri udah optimistic dari
  sebelumnya (`recentlyMergedIds`, hilang instan) - yang masih delay itu
  banner ahead/behind RepoPicker buat branch yang habis di-merge.
  `markMergedLocal()` sekarang set `ahead_by: 0` (branch itu emang sudah
  pasti 0 ahead abis semua commit-nya masuk ke base) & `behind_by + 1`
  SEKETIKA abis merge sukses, kalau branch yang di-merge itu branch aktif
  yang lagi dipakai.

### Files changed (batch 7)
- `src/context/RepoContext.js`
- `src/components/PullRequestsModal.js`

---

## 11 Agustus 2026 (batch 6 - optimistic ahead count, tema Pink Soft)

### Fixed
- **Ahead/behind masih delay walau udah polling.** Jawaban langsung: bisa
  dibikin optimistic kayak status PR, dan aman - beda dari existingPR
  (boolean yang ambigu kalau di-flip balik replica lag), kenaikan
  `ahead_by` abis commit itu fakta yang app sendiri tahu pasti (baru aja
  push commit itu sendiri). `bumpAheadLocal()` sekarang naikkan angka
  ahead di layar SEKETIKA abis commit sukses, tanpa nunggu network call
  apapun - nol delay yang kerasa user. `aheadFloorRef` jaga biar
  `refreshSync` yang jalan di belakang layar tidak bisa menurunkan balik
  angka yang sudah benar (kalau replica GitHub masih lag & balikin angka
  lebih rendah) - selalu `Math.max(hasil fetch, floor)`.

### Changed
- **Tema Forest diganti Pink Soft** (pink lembut) sesuai permintaan.

### Files changed (batch 6)
- `src/context/RepoContext.js`
- `src/screens/UploadScreen.js`
- `src/theme.js`

---

## 11 Agustus 2026 (batch 5 - polling delay, branch master hilang, kontras tema)

### Fixed
- **Ahead/behind delay abis push (laporan "harusnya ahead 1, delay").**
  Retry sebelumnya cuma SATU kali di 4 detik - kalau replica GitHub belum
  sinkron persis di situ, macet sampai refresh manual. Diganti polling
  berkali-kali dengan backoff (1.5s/3s/6s/9s, total ~19.5 detik) dan
  BERHENTI LEBIH AWAL begitu `ahead_by` sudah sesuai yang diharapkan -
  `commitPush` sekarang kirim `minAhead` (ahead sebelum push + 1) supaya
  polling tahu persis kapan boleh berhenti, bukan asal nunggu.
- **Branch "master"/default hilang dari dropdown.** Root cause: list
  branch disortir alfabetis polos - di repo dengan banyak branch, default
  branch bisa jatuh di tengah/bawah dan ketutup keterbatasan tinggi
  dropdown (ditambah nested ScrollView yang gesture-nya kadang tidak
  mulus). Default branch sekarang SELALU dipin di posisi paling atas +
  badge "default", jadi tidak pernah butuh scroll buat mencarinya.
- **Kontras tema Forest & Lavender.** Accent lama di kedua tema (lime
  #65A30D di Forest, ungu #8B5CF6 di Lavender) kontrasnya rendah buat
  teks putih di tombol - digelapkan (Forest -> #15803D, Lavender ->
  #7C3AED); lime & ungu terang lama dipindah jadi accentAlt (tetap
  kepakai buat aksen/aurora). `inkFaint` di kedua tema juga digelapkan,
  teks sekunder sebelumnya nyaris tidak kebaca di atas background terang.

### Files changed (batch 5)
- `src/context/RepoContext.js`
- `src/components/RepoPicker.js`
- `src/screens/UploadScreen.js`
- `src/theme.js`

---

## 11 Agustus 2026 (batch 4 - PR badge optimistic, path traversal, tema baru)

### Fixed
- **Notif "Buat PR" masih nyangkut padahal PR baru dibuat.** Root cause:
  `RepoPicker.js` cuma manggil `refreshSync` abis PR dibuat, yang baca
  ulang `GET /pulls?head=` ke GitHub - endpoint itu baca dari read-replica
  yang lag beberapa detik setelah write (`PullRequestsModal.js` di file
  yang sama sudah benar - optimistic-insert `pr` ke state lokal tanpa
  nunggu refetch). Ditambahkan `markPRCreatedLocal()` di `RepoContext` -
  dipanggil di `onCreated` (RepoPicker) & abis `createPullRequest()`
  sukses (UploadScreen), badge langsung berubah seketika. Dilindungi dari
  race: `refreshSync` yang jalan setelahnya tidak lagi menimpa balik ke
  `false` kalau status sudah optimistic `true` (replica lag GitHub bisa
  balikin "belum ada PR" walau sebenarnya sudah ada).
- **Security: path traversal di 3 tempat input path manual** (belum
  ke-cover proteksi Zip Slip yang cuma dipasang di jalur zip/
  `ignoreRules.js`): `CreateEntryModal.js` (buat file/folder baru),
  `FolderBrowserModal.js` (`customPath`), `UploadScreen.js`
  (`withTargetDir`/`targetDir`) - ketiganya cuma trim + buang leading/
  trailing slash, segmen `../` di tengah path tidak difilter. Sekarang
  ketiganya pakai `sanitizeZipEntryPath()` (satu logic yang sama dengan
  jalur zip, bukan proteksi terpisah).

### Added
- **2 tema baru:** Forest (hijau forest tua + lime terang segar sebagai
  accent) dan Sunset (orange + merah hangat). Otomatis muncul di
  Settings - registry tema (`THEMES`) dibaca dinamis, tidak perlu ubah
  komponen manapun.

### Files changed (batch 4)
- `src/theme.js`
- `src/context/RepoContext.js`
- `src/components/RepoPicker.js`
- `src/components/CreateEntryModal.js`
- `src/components/FolderBrowserModal.js`
- `src/screens/UploadScreen.js`

---

## 11 Agustus 2026 (batch 3 - branch delete lintas-modal, progress bar, modal popup)

### Fixed
- **Branch delete "not-found" - branch lama masih nongol.** Root cause:
  `branches` state LOKAL per-instance RepoPicker, cuma di-refetch pas
  `active.owner/repo` ganti. `doDeleteBranch` di `PullRequestsModal.js`
  cuma update `active` (kalau branch itu lagi dipakai) - list branches
  RepoPicker tidak pernah disentuh. Dipindah ke `RepoContext` (pola sama
  seperti `compare`/`existingPR`): `branches`, `loadBranches`,
  `removeBranchLocal`, `addBranchLocal` sekarang satu sumber dipakai
  RepoPicker & PullRequestsModal - hapus/tambah branch dari modal manapun
  langsung sinkron ke semuanya, tanpa perlu ganti repo dulu.

### Added
- **Progress bar commit & push.** `commitFiles()` (`github.js`) sekarang
  terima `onProgress(current, total, label)`, dipanggil tiap batch blob
  (10 file paralel) + tahap tree/commit/update-ref selesai. `LoadingModal`
  dapat prop `progress` (0-1) - render progress bar + label "X/Y" gantiin
  spinner+teks statis.
- **Hasil commit & push jadi modal popup, bukan notif nempel di layar.**
  Sukses & error abis commit sekarang `appAlert()` (modal), bukan
  `InfoBanner`/`ErrorBanner` inline yang gampang kelewat.

### Files changed (batch 3)
- `src/context/RepoContext.js`
- `src/components/RepoPicker.js`
- `src/components/PullRequestsModal.js`
- `src/components/AppModals.js`
- `src/screens/UploadScreen.js`
- `src/lib/github.js`

---

## 11 Agustus 2026 (batch 2 - pull-to-refresh & delay ahead/behind)

### Fixed
- **Pull-to-refresh Files cuma refresh file, bukan status branch.**
  `onPullRefresh` sekarang juga panggil `refreshSync` (banner
  ahead/behind + status PR), bukan cuma `loadTree()`.
- **Tab Upload sama sekali tidak ada pull-to-refresh.** Ditambahkan
  (refresh existingPaths/folders + refreshSync sekaligus).
- **Delay ahead/behind setelah create PR/commit.** Ini kombinasi dua hal:
  (1) sebelumnya memang belum ada mekanisme retry sama sekali, (2)
  GitHub REST API (`/compare`, `/pulls`) sendiri baca dari read-replica
  yang butuh beberapa detik buat konsisten setelah write - ini
  keterbatasan API GitHub, bukan bug klien, dan tidak bisa dipaksa
  instan dari sisi app manapun. Mitigasi yang ditambahkan:
  - Auto-retry sekali (~4 detik setelah refresh) tiap `refreshSync`
    dipanggil, supaya kebanyakan kasus replica-lag self-correct tanpa
    aksi tambahan dari user.
  - Tombol refresh manual (ikon 🔄) langsung di banner ahead/behind buat
    paksa cek ulang kapan saja, tidak perlu lagi akal-akalan ganti
    branch bolak-balik.

### Changed
- Teks banner diganti dari "N commit lebih maju, M commit tertinggal"
  jadi format `ahead N / behind M` (istilah git asli, lebih ringkas).
- Kalau ahead 0 & behind 0 (branch sudah sync), banner ganti hijau
  dengan teks "Branch sync - ahead 0 / behind 0" - beda jelas dari
  status kuning (masih ada perbedaan).

### Files changed (batch 2)
- `src/context/RepoContext.js`
- `src/components/RepoPicker.js`
- `src/screens/BrowserScreen.js`
- `src/screens/UploadScreen.js`

---

## 11 Agustus 2026 (batch 1 - lintas-tab & branch)

### Fixed
- **Banner "N commit lebih maju" masih 0 setelah push.** `compareCommits`
  di `RepoPicker.js` cuma di-fetch waktu repo/branch aktif ganti, push ke
  branch yang sama tidak pernah men-trigger-nya. `refreshSync` sekarang
  dipanggil manual dari `UploadScreen` persis setelah commit sukses.
- **Notif "Buat Pull Request" basi lintas tab.** `RepoPicker` dipasang 2
  instance terpisah (Files & Upload), masing-masing punya state
  `compare`/`existingPR` sendiri-sendiri. PR yang dibuat dari satu tab
  tidak pernah diketahui instance di tab lain. State dipindah ke satu
  tempat di `RepoContext` (`compare`, `compareLoading`, `existingPR`,
  `refreshSync`) - dipakai bareng semua `RepoPicker`, dan diperbarui dari
  `UploadScreen` setelah commit push maupun setelah create PR (dua jalur
  PR: banner RepoPicker & tombol offer di UploadScreen).
- **Pesan commit ketimpa keyboard di Upload.** `KeyboardAvoidingView`
  Android sebelumnya `behavior={undefined}` (cuma iOS yang dapat
  `padding`), murni mengandalkan `android.softwareKeyboardLayoutMode:
  "resize"` yang tidak konsisten kerja di device tertentu/edge-to-edge.
  Sekarang Android juga pakai `behavior="height"`.
- **Warning `"git-compare" is not a valid icon name for family
  "feather"`.** Diganti ke `shuffle` (valid di Feather).

### Added
- **Tambah branch baru** langsung dari dropdown branch RepoPicker
  (input nama + tombol +), dibuat dari SHA branch yang lagi aktif.
- **Hapus branch** langsung dari dropdown branch (ikon tempat sampah per
  baris), dengan proteksi tidak bisa hapus default branch atau branch
  yang lagi dipakai. (Hapus branch pasca-merge PR di `PullRequestsModal`
  tetap ada, tidak berubah.)
- `github.js`: fungsi baru `createBranchRef(token, owner, repo, name,
  fromSha)`.

### Clarified (bukan bug)
- 1 commit "tertinggal" di branch fitur setelah PR di-merge tanpa hapus
  branch: normal. Ref branch fitur tidak otomatis maju ke commit merge
  baru di default branch - makanya kelihatan "behind". Disarankan hapus
  branch setelah merge (sudah bisa lewat PullRequestsModal atau
  RepoPicker sekarang).

### Files changed
- `src/context/RepoContext.js`
- `src/components/RepoPicker.js`
- `src/screens/UploadScreen.js`
- `src/lib/github.js`
