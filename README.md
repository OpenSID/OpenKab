<h1 align="center">Selamat datang di OpenKab! 👋</h1>

### Apa itu OpenKab?
OpenKab (https://github.com/OpenSID/OpenKab) adalah aplikasi yang bisa digunakan oleh Pemerintah Kabupaten di Seluruh Indonesia. Aplikasi terintegrasi dengan OpenSID sebagai sumber data maka ini sangat berguna untuk menampilkan statistik di Kelurahan/desa, juga statistik Penduduk, Statistik Kesehatan, Statistik Pendidikan dan Statistik lainnya. Upaya ini adalah sebagai bentuk transparansi dan Keterbukaan Informasi Publik yang dilakukan Pemerintah Kabupaten kepada seluruh rakyat di wilayahnya .

### 💻 DEMO
Demo aplikasi OpenKab dapat dilihat di https://devopenkab.opendesa.id. Versi yang terlihat di demo itu adalah rilis terkini.

Modul administrasi OpenKab demo dapat diaskses pada [https://devopenkab.opendesa.id/index.php/login](https://devopenkab.opendesa.id/login). 
- Username = admin@gmail.com
- Password = Admin100%

### 🔐 SSO Akses Panel Admin OpenSID

OpenKab mendukung Single Sign-On (SSO) sehingga administrator dapat masuk ke panel admin OpenSID desa tanpa login ulang (wajib sesi aktif + 2FA). Dokumen integrasi:
- Deployment/operasional OpenKab: [`docs/sso-opensid.md`](docs/sso-opensid.md)
- Kontrak API sisi OpenKab: `specs/001-opensid-sso-access/contracts/openkab-sso-api.md`
- Kontrak integrasi sisi OpenSID (repo terpisah): `specs/001-opensid-sso-access/contracts/opensid-sso-contract.md`
