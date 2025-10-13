<p align="center">
  <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-router" viewBox="0 0 16 16">
    <path d="M2 12a1 1 0 0 0-1 1v1.5a.5.5 0 0 0 .5.5H3v-2h10v2h1.5a.5.5 0 0 0 .5-.5V13a1 1 0 0 0-1-1H2Zm3.5 1.5a.5.5 0 0 1-1 0V13a.5.5 0 0 1 1 0v.5Zm2 0a.5.5 0 0 1-1 0V13a.5.5 0 0 1 1 0v.5Zm2 0a.5.5 0 0 1-1 0V13a.5.5 0 0 1 1 0v.5ZM14 9H2V7h12v2Zm-1-3H3V5h10v1Zm-1.5-4a.5.5 0 0 1 .5.5V3H4V2.5a.5.5 0 0 1 1 0V3h6v-.5a.5.5 0 0 1 .5-.5Z"/>
  </svg>
</p>

<h1 align="center" style="font-weight:700;">ISP-MANAGER</h1>
<p align="center" style="color:gray;"> MANAGE YOUR FIBER OPTIK NETWORK </p>


<p align="center">
<!--<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>-->
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Install Laravel 12
- composer create-project laravel/laravel isp-manager
- cd isp-manager

## Install dependencies
- composer require spatie/laravel-permission
- composer require laravel/breeze --dev
- composer require evilfreelancer/routeros-api-php
- composer require barryvdh/laravel-dompdf
- composer require phpseclib/phpseclib:~3.0
- composer require maatwebsite/excel

## Setup Breeze (untuk authentication UI)
- php artisan breeze:install blade
- php artisan migrate
- npm install && npm run dev

## SETUP .ENV dan DB nya
APP_NAME="ISP Manager"
APP_ENV=local
APP_KEY= ( otomatis Berubah )
APP_DEBUG=true
APP_TIMEZONE=Asia/Makassar ( Sesuaikan )
APP_URL=http://isp-manager.test

##Open terminal kemdudian
- php artisan key:generate
- php artisan migrate:fresh --seed
- php artisan db:seed

## USER LOGIN
- Email: admin@ispmanager.test
- Password: password

# CATATAN PENTING, SILAHKAN DIBACA DENGAN TELITI
- Silahkan dipergunakan sebagaimana mestinya, aplikasi tidak diperjualbelikan, Apabila ada yang memperjual belikan, maka bukan tanggung jawab kami.
- apabila ingin mengembangkan, silahkan dikembangkan kembali.
- Saat ini tritegrasi dengan TRIPAY.
- Database gunakan Postgresql

# SCREENSHOT
![alt text](https://github.com/deindr4/isp-manager/blob/main/documentasi/login.JPG?raw=true)

![alt text](https://github.com/deindr4/isp-manager/blob/main/documentasi/dashboard.JPG?raw=true)

![alt text](https://github.com/deindr4/isp-manager/blob/main/documentasi/map.JPG?raw=true)

