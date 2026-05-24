# ==========================================================================
# PANDUAN LENGKAP DOCKER & LARAVEL DEPLOYMENT (FULL BASH SCRIPTABLE)
# ==========================================================================

# ==========================================================================
# BAGIAN 1: PERSIAPAN DOCKER DI VPS [cite: 1]
# ==========================================================================

# --------------------------------------------------------------------------
# Langkah 1: Gandeng Server ke Server Resmi Docker [cite: 1]
# Kita perlu memperbarui indeks paket Linux dan mengunduh sertifikat keamanan [cite: 1]
# agar VPS Ndoro diizinkan mengambil Docker langsung dari pusatnya[cite: 1].
# Jalankan perintah ini satu per satu, Ndoro[cite: 2]:
# --------------------------------------------------------------------------

# 1. Perbarui daftar paket bawaan [cite: 2]
sudo apt-get update

# 2. Instal aplikasi pendukung unduhan aman [cite: 2]
sudo apt-get install ca-certificates curl -y [cite: 2]

# 3. Buat folder untuk menyimpan kunci keamanan Docker [cite: 2]
sudo install -m 0755 -d /etc/apt/keyrings [cite: 2]

# 4. Ambil kunci resmi dari Docker [cite: 2]
sudo curl -fsSL https://download.docker.com/linux/debian/gpg -o /etc/apt/keyrings/docker.asc [cite: 2]
sudo chmod a+r /etc/apt/keyrings/docker.asc [cite: 2]

# (Catatan: Perintah di atas disesuaikan untuk OS Debian seperti bawaan server [cite: 2]
# Ndoro sebelumnya. Jika Ndoro saat reinstall memilih Ubuntu, sistem akan [cite: 2]
# otomatis menyesuaikannya dengan aman)[cite: 2].


# --------------------------------------------------------------------------
# Langkah 2: Daftarkan Docker ke Sumber Aplikasi Linux [cite: 3]
# Ketik perintah panjang ini (salin semua lalu paste langsung ke terminal) [cite: 3]
# untuk mendaftarkan Docker ke dalam daftar belanjaan sistem (repository)[cite: 3]:
# --------------------------------------------------------------------------

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/debian \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null [cite: 3, 4]


# --------------------------------------------------------------------------
# Langkah 3: Eksekusi Instalasi Utama Docker [cite: 4]
# Sekarang, mari kita panggil Docker dan pasang ke dalam jantung VPS Ndoro[cite: 4]:
# --------------------------------------------------------------------------

# Perbarui indeks agar membaca daftar Docker yang baru didaftarkan [cite: 4]
sudo apt-get update [cite: 4]

# Jalankan instalasi Docker beserta Compose (alat pengatur port kita nanti) [cite: 4]
sudo apt-get install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin -y [cite: 4]


# --------------------------------------------------------------------------
# Langkah 4: Ritual Pembuktian Kesuksesan [cite: 4]
# Untuk memastikan apakah Docker sudah hidup dan bernapas di VPS Ndoro, [cite: 4]
# silakan ketik perintah ini[cite: 4]:
# --------------------------------------------------------------------------

sudo systemctl status docker [cite: 4]


# ==========================================================================
# BAGIAN 2: PERSIAPAN DOCKER DI PROJECT [cite: 4]
# ==========================================================================

# --------------------------------------------------------------------------
# Langkah 1: Tarik Proyek dari GitHub ke VPS [cite: 4]
# Sekarang, masuk ke terminal VPS Ndoro (root@ngumar-porto:~#), [cite: 4]
# mari kita buat folder khusus dan tarik kodenya dari GitHub[cite: 4]:
# --------------------------------------------------------------------------

# 1. Masuk ke folder standar web Linux [cite: 5]
cd /var/www [cite: 5]

# 2. Tarik proyek dari GitHub Ndoro (Ganti dengan URL Repo Ndoro) [cite: 5]
git clone https://github.com/username_github_ndoro/app_first.git [cite: 5]

# 3. Masuk ke dalam folder proyek yang baru ditarik [cite: 5]
cd app_first [cite: 5]


# --------------------------------------------------------------------------
# Langkah 2: Racik Berkas Sakti docker-compose.yml [cite: 5]
# Di dalam folder /var/www/app_first tersebut, kita akan membuat [cite: 5]
# satu file konfigurasi Docker[cite: 5].
# Ketik perintah ini untuk membuka editor teks[cite: 6]:
#
# nano docker-compose.yml [cite: 6]
#
# Lalu, salin dan tempel (paste) kode sakti di bawah ini ke dalamnya[cite: 6].
# Konfigurasi ini sudah otomatis membungkus Aplikasi Laravel, Nginx, [cite: 7]
# dan Database MySQL menjadi satu kesatuan[cite: 7]:
# --------------------------------------------------------------------------

cat << 'EOF' > docker-compose.yml
version: '3.8' # version adalah instruksi untuk menentukan versi skema Docker Compose yang digunakan agar sesuai dengan fitur Docker Engine di VPS. [cite: 7]

services: # services adalah blok utama yang digunakan untuk mendefinisikan daftar kontainer (layanan) yang akan dijalankan bersamaan. [cite: 8]

  # 1. Wadah untuk Aplikasi Laravel (PHP 8.4) [cite: 9]
  app: # app adalah nama layanan internal dalam jaringan Docker yang merujuk pada kontainer aplikasi Laravel sampeyan. [cite: 9]
    image: php:8.4-fpm # image adalah instruksi untuk mengambil base image resmi PHP versi 8.4 dengan varian FPM (FastCGI Process Manager) dari Docker Hub. [cite: 10]
    container_name: app_pertama_backend # container_name adalah instruksi untuk memberikan nama absolut pada kontainer saat berjalan agar mudah diidentifikasi lewat perintah 'docker ps'. [cite: 11]
    volumes: # volumes adalah instruksi untuk melakukan sinkronisasi atau pemetaan direktori antara VPS (host) dengan bagian dalam kontainer. [cite: 12]
      - .:/var/www # .:/var/www adalah parameter pemetaan di mana titik (.) berarti seluruh folder project saat ini di VPS akan dimasukkan ke dalam folder /var/www di dalam kontainer. [cite: 13]
    working_dir: /var/www # working_dir adalah instruksi untuk menetapkan direktori kerja utama di dalam kontainer, sehingga perintah seperti 'php artisan' otomatis dieksekusi di folder ini. [cite: 14]
    depends_on: # depends_on adalah instruksi untuk mengatur urutan jalannya kontainer berdasarkan ketergantungan antar layanan. [cite: 15]
      - db # - db adalah parameter yang memastikan kontainer database (db) harus menyala lebih dahulu sebelum kontainer aplikasi (app) ini dijalankan. [cite: 16]

  # 2. Wadah untuk Satpam Gerbang (Nginx - Menjaga Port 80 VPS) [cite: 17]
  nginx: # nginx adalah nama layanan internal untuk server web yang bertugas menerima dan mengarahkan lalu lintas data (request HTTP). [cite: 17]
    image: nginx:alpine # image: nginx:alpine adalah instruksi untuk menggunakan image Nginx berbasis distro Alpine Linux yang ukurannya sangat kecil dan ringan. [cite: 18]
    container_name: app_pertama_nginx # container_name adalah nama spesifik yang diberikan untuk kontainer server web Nginx ini. [cite: 19]
    ports: # ports adalah instruksi untuk membuka dan memetakan jalur akses (port forwarding) dari luar VPS ke dalam kontainer. [cite: 20]
      - "80:80" # "80:80" adalah parameter di mana angka kiri (80) adalah port publik VPS, dan angka kanan (80) adalah port internal milik kontainer Nginx. [cite: 21]
    volumes: # volumes adalah instruksi pemetaan file konfigurasi dan source code agar bisa dibaca oleh Nginx. [cite: 22]
      - .:/var/www # .:/var/www adalah pemetaan source code project ke /var/www kontainer Nginx agar server bisa mencari file index.php atau aset statis. [cite: 23]
      - ./docker-nginx.conf:/etc/nginx/conf.d/default.conf # ./docker-nginx.conf:... adalah instruksi untuk menimpa konfigurasi bawaan Nginx dengan file konfigurasi custom milik sampeyan yang ada di VPS. [cite: 24]
    depends_on: # depends_on adalah instruksi untuk mengatur urutan proses manajemen kontainer server web. [cite: 25]
      - app # - app adalah parameter yang memastikan Nginx baru menyala setelah kontainer PHP-FPM (app) siap menerima operan request codingan Laravel. [cite: 26]

  # 3. Wadah untuk Database Toko (MySQL / MariaDB) [cite: 27]
  db: # db adalah nama layanan internal yang bertindak sebagai server penyimpanan data relational project sampeyan. [cite: 27]
    image: mysql:8.0 # image adalah instruksi untuk menarik software database MySQL versi stabil 8.0 dari repository Docker Hub. [cite: 28]
    container_name: app_pertama_db # container_name adalah nama spesifik untuk mempermudah manajemen data dan proses backup-restore kontainer database ini. [cite: 29]
    environment: # environment adalah instruksi untuk menyuntikkan variabel lingkungan (environment variables) ke dalam sistem operasi kontainer. [cite: 30]
      MYSQL_ROOT_PASSWORD: password_vps_ndoro # MYSQL_ROOT_PASSWORD adalah variabel wajib dari MySQL untuk menetapkan password super-admin (root) di dalam database. [cite: 31]
      MYSQL_DATABASE: db_app_pertama # MYSQL_DATABASE adalah variabel opsional yang otomatis membuatkan satu database baru dengan nama tersebut saat kontainer pertama kali dibuat. [cite: 32]
    ports: # ports adalah instruksi pemetaan jalur koneksi data. [cite: 33]
      - "3306:3306" # "3306:3306" adalah parameter yang meneruskan port 3306 milik VPS (kiri) ke port 3306 database di dalam kontainer (kanan), agar bisa diremote dari luar jika diperlukan. [cite: 33]
EOF

# Catatan Tambahan: Jika mengedit manual via server, simpan dengan CTRL + O -> Enter -> CTRL + X. [cite: 81]


# --------------------------------------------------------------------------
# Langkah 3: Racik Berkas Konfigurasi Nginx Mini (docker-nginx.conf) [cite: 34]
# Karena kita pakai Docker, kita tidak menyentuh folder /etc/nginx/ milik VPS[cite: 34].
# Kita cukup buat file konfigurasi Nginx kecil langsung di dalam folder proyek ini[cite: 35].
# Ketik perintah ini untuk membuka editor teks: [cite: 35]
#
# nano docker-nginx.conf [cite: 35]
#
# Isi dengan konfigurasi standar Laravel berikut: [cite: 35]
# --------------------------------------------------------------------------

cat << 'EOF' > docker-nginx.conf
server { # server adalah blok utama (virtual host) yang digunakan untuk mendefinisikan konfigurasi server web untuk domain atau IP tertentu. [cite: 35]
    listen 80; # listen adalah instruksi untuk menentukan port mana yang akan didengar oleh Nginx untuk menerima request. [cite: 36]
    # - Value saat ini: 80, artinya Nginx mendengarkan lalu lintas HTTP standar. [cite: 37]
    # - Bisa diganti dengan: 443 (jika nanti menggunakan HTTPS/SSL), atau port custom lain seperti 8080. [cite: 38]

    index index.php index.html; # [cite: 38]
    # index adalah instruksi untuk menentukan urutan file prioritas yang akan dibaca pertama kali sebagai halaman utama. [cite: 39]
    # - Value saat ini: index.php diikuti index.html, karena ini project Laravel yang mengutamakan PHP. [cite: 40]
    # - Bisa diganti dengan: Menghapus index.html jika murni hanya ingin mengeksekusi PHP, atau menambah index.htm jika diperlukan. [cite: 41]

    error_log  /var/log/nginx/error.log; # error_log adalah instruksi untuk menentukan jalur (path) penyimpanan file log jika terjadi error pada server. [cite: 42]
    # - Value saat ini: /var/log/nginx/error.log, yang merupakan folder log standar di dalam kontainer Nginx. [cite: 43]
    # - Bisa diganti dengan: Jalur lain di dalam kontainer, atau diisi 'off' jika ingin mematikan pencatatan log error (tidak disarankan). [cite: 44]

    access_log /var/log/nginx/access.log; # access_log adalah instruksi untuk menentukan jalur penyimpanan log yang mencatat setiap request masuk dari pengunjung. [cite: 45]
    # - Value saat ini: /var/log/nginx/access.log, mencatat semua data traffic (IP, status code, device). [cite: 46]
    # - Bisa diganti dengan: 'off' jika ingin menghemat penyimpanan VPS agar file log tidak membengkak. [cite: 47]

    root /var/www/public; # [cite: 47]
    # root adalah instruksi untuk menentukan folder root (induk) tempat Nginx mencari file yang diminta oleh browser. [cite: 48]
    # - Value saat ini: /var/www/public, karena arsitektur Laravel mewajibkan entry point utama berada di folder 'public'. [cite: 49]
    # - Bisa diganti dengan: /var/www/html (jika menggunakan PHP native), atau direktori lain sesuai dengan tempat file utama project sampeyan berada di dalam kontainer. [cite: 50]

    location ~ \.php$ { # location ~ \.php$ adalah blok pencocokan menggunakan Regex yang mendeteksi jika ada request file yang berakhiran ekstensi '.php' untuk diproses khusus. [cite: 51]
        try_files $uri =404; # try_files adalah instruksi untuk memeriksa apakah file PHP yang dicari benar-benar ada di folder root VPS sebelum dieksekusi. [cite: 52]
        # - Value saat ini: $uri =404, artinya jika file PHP tidak ditemukan, Nginx langsung melempar error 404 (Not Found) daripada membiarkan server crash. [cite: 53]
        # - Bisa diganti dengan: Menghapus '=404' dan mengarahkannya ke fallback lain, namun bawaan ini sudah paling aman dari celah keamanan script injection. [cite: 54]

        fastcgi_split_path_info ^(.+\.php)(/.+)$; # fastcgi_split_path_info adalah instruksi berbasis regex untuk memisahkan nama file PHP dengan baris argumen URL tambahan (path info) setelah tanda '.php'. [cite: 55]
        # - Value saat ini: Rumus regex standar FastCGI Nginx untuk memisahkan URL agar framework bisa membaca route dengan benar. [cite: 56]
        # - Bisa diganti dengan: Tidak perlu diganti karena ini regulasi global pemrosesan PHP pada Nginx. [cite: 57]

        fastcgi_pass app:9000; # [cite: 57]
        # fastcgi_pass adalah instruksi krusial untuk melempar/mengoper pemrosesan file PHP ke kontainer penampung PHP-FPM. [cite: 58]
        # - Value saat ini: app:9000. [cite: 58]
        # 'app' merujuk pada nama layanan kontainer Laravel di docker-compose sampeyan, dan '9000' adalah port default PHP-FPM. [cite: 59]
        # - Bisa diganti dengan: Nama layanan lain jika di docker-compose sampeyan mengubah nama layanan app (misal: backend:9000), atau menggunakan socket file jika tidak pakai Docker. [cite: 60]

        fastcgi_index index.php; # fastcgi_index adalah instruksi untuk menetapkan file default yang digunakan sebagai basis jika request hanya mengarah ke sebuah direktori di dalam FastCGI. [cite: 61]
        # - Value saat ini: index.php sebagai standard global script PHP. [cite: 62]
        # - Bisa diganti dengan: Nama file php utama lain jika sampeyan tidak menggunakan standard Laravel. [cite: 63]

        include fastcgi_params; # [cite: 63]
        # include adalah instruksi untuk memasukkan (import) file konfigurasi parameter bawaan Nginx agar FastCGI mengenali variabel server. [cite: 64]
        # - Value saat ini: fastcgi_params, file internal Nginx yang berisi pemetaan data seperti REMOTE_ADDR, REQUEST_METHOD, dll. [cite: 65]
        # - Bisa diganti dengan: fastcgi.conf (tergantung varian instalasi Nginx, tapi di Docker Alpine menggunakan 'fastcgi_params'). [cite: 66]

        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; # [cite: 66]
        # fastcgi_param adalah instruksi untuk mendefinisikan variabel SCRIPT_FILENAME yang akan dikirim ke PHP-FPM. [cite: 67]
        # - Value saat ini: $document_root$fastcgi_script_name, yang berarti menggabungkan path root (/var/www/public) dengan nama file script php yang dipanggil agar PHP-FPM tahu lokasi pasti file tersebut. [cite: 68]
        # - Bisa diganti dengan: Path absolut langsung seperti /var/www/public$fastcgi_script_name, tapi menggunakan variabel $document_root jauh lebih dinamis. [cite: 69]

        fastcgi_param PATH_INFO $fastcgi_path_info; # [cite: 69]
        # fastcgi_param adalah instruksi untuk mengirimkan data informasi path URL tambahan ke sistem PHP. [cite: 70]
        # - Value saat ini: $fastcgi_path_info, variabel hasil potongan dari perintah 'fastcgi_split_path_info' di atas untuk kebutuhan routing Laravel. [cite: 71]
        # - Bisa diganti dengan: Bisa dihapus jika project sampeyan murni PHP native sederhana tanpa sistem routing/clean URL. [cite: 72]
    } # [cite: 73]

    location / { # location / adalah blok pencocokan default yang menangani semua request masuk yang tidak berakhiran '.php' (seperti request asset, folder, atau rute URL biasa). [cite: 73]
        try_files $uri $uri/ /index.php?$query_string; # try_files adalah instruksi utama untuk sistem routing (Pretty URLs) milik Laravel. [cite: 74]
        # - Value saat ini: Pertama, Nginx mencari file fisik sesuai URL ($uri). Jika tidak ada, mencari folder ($uri/). [cite: 75]
        # Jika tetap tidak ada, request dioper ke /index.php dengan menyertakan parameter query string-nya. [cite: 76]
        # - Bisa diganti dengan: $uri $uri/ =404 (jika website sampeyan hanya html statis biasa dan tidak mau diarahkan ke index.php jika file tidak ada). [cite: 77]

        gzip_static on; # gzip_static adalah instruksi untuk menyuruh Nginx langsung mengirimkan file versi kompresi (.gz) jika file pra-kompresi tersebut tersedia di VPS. [cite: 78]
        # - Value saat ini: on, bermanfaat menghemat bandwidth VPS dan mempercepat loading asset seperti CSS atau JS yang sudah di-minify. [cite: 79]
        # - Bisa diganti dengan: off, jika sampeyan tidak melakukan pra-kompresi gzip pada file asset project. [cite: 80]
    } # [cite: 81]
}
EOF

# Catatan Tambahan: Jika mengedit manual via server, simpan dengan CTRL + O -> Enter -> CTRL + X. [cite: 81]


# --------------------------------------------------------------------------
# Langkah 4: Nyalakan Kontainer! [cite: 81]
# Ini saatnya keajaiban Docker bekerja[cite: 81].
# Cukup ketik satu mantra ini di terminal VPS Ndoro[cite: 82]:
# --------------------------------------------------------------------------

docker compose up -d [cite: 82]