/**
 * lib/ads.js
 * Satu titik konfigurasi AdMob (react-native-google-mobile-ads) - init SDK
 * sekali (mobileAds().initialize() idempotent lewat cache promise di
 * bawah), dan Ad Unit ID buat tiap format iklan.
 *
 * TAHAP TES (permintaan Zen): selama __DEV__ SELALU pakai TestIds resmi
 * dari Google, BUKAN Ad Unit ID asli - klik/impresi dari device testing
 * ke Ad Unit ID production bisa dianggap "invalid traffic" oleh Google
 * dan berisiko akun AdMob kena suspend. Ganti PROD_*_ID di bawah ke ID
 * asli dari akun AdMob pas sudah siap rilis, __DEV__ akan otomatis false
 * di build release (APK/AAB/IPA production) jadi tidak perlu ubah logic
 * pemilihannya lagi.
 *
 * BATCH 16 (permintaan Zen "split string, biar ga langsung ketauan
 * ID-nya"): ID production dipecah jadi 2 potongan & digabung pas
 * runtime (`.join('/')`) - bukan ditulis utuh sebagai satu string
 * literal. Ini BUKAN enkripsi (orang yang niat serius tetap bisa
 * nyambung-nyambungin manual), tujuannya cuma nge-gagalin pencarian
 * teks polos ("Ctrl+F cari ca-app-pub-...") di bundle JS yang udah
 * di-minify - which sebelumnya masih ketemu instan walau bundle-nya
 * sudah di-minify (minifier gak nyentuh isi string literal, cuma nama
 * variabel di sekitarnya). TestIds dari Google TIDAK di-split - itu ID
 * publik resmi buat testing, bukan sesuatu yang perlu disembunyikan.
 */
import { Platform } from 'react-native';
import mobileAds, { TestIds } from 'react-native-google-mobile-ads';

// TODO Zen: isi dengan Ad Unit ID asli dari akun AdMob sebelum rilis.
// Format: [prefix "ca-app-pub-<publisher id>", "<ad unit number>"].
const PROD_INTERSTITIAL_ID = Platform.select({
  android: ['ca-app-pub-8421711284376884', '4573735012'].join('/'),
  ios: ['ca-app-pub-XXXXXXXXXXXXXXXX', 'ZZZZZZZZZZ'].join('/'), // belum build iOS, biarin placeholder
  default: TestIds.INTERSTITIAL,
});

const PROD_BANNER_ID = Platform.select({
  android: ['ca-app-pub-8421711284376884', '2685938273'].join('/'),
  ios: ['ca-app-pub-XXXXXXXXXXXXXXXX', 'BBBBBBBBBB'].join('/'), // belum build iOS, biarin placeholder
  default: TestIds.BANNER,
});

export const AD_UNIT_INTERSTITIAL = __DEV__ ? TestIds.INTERSTITIAL : PROD_INTERSTITIAL_ID;
export const AD_UNIT_BANNER = __DEV__ ? TestIds.BANNER : PROD_BANNER_ID;

let initPromise = null;
/** Idempotent - aman dipanggil dari beberapa komponen, SDK cuma di-init sekali. */
export function initAds() {
  if (!initPromise) initPromise = mobileAds().initialize();
  return initPromise;
}
