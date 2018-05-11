# Symfony Youtube Analytics
Symfony Youtube Analytics merupakan bundle sederhana untuk melakukan otorisasi dan request performance dari channel youtube.


## Cara Install
Morphological analyzer dapat diinstall dengan [Composer](https://getcomposer.org/). 
```
php composer.phar require vortexgin/youtube-analytics:1.*
``` 

Jika Anda masih belum memahami bagaimana cara menggunakan Composer, silahkan baca [Getting Started with Composer](https://getcomposer.org/doc/00-intro.md).

### Konfigurasi
```
vortexgin_youtube_analytics: 
    channel_id: __CHANNEL_ID__ 
    auth_file: %kernel.root_dir%/../db/google-auth.json 
    config_file: __CONFIG_FILE__ 
    callback_url: __CALLBACK_URL__ 
```

__CHANNEL_ID__ berisi channel id dari akun youtube

__AUTH_FILE__ berisi fixed path dari file authentifikasi dimana isi dari file ini adalah access token hasil dari otorisasi akan disimpan. 

__CONFIG_FILE__ berisi fixed path dari file configurasi OAUTH 2.0 yang dapat digenerate di [Google Developer Console](https://console.developers.google.com/apis/credentials)

__CALLBACK_URL__ berisi url dari callback url. Url ini berelasi dengan route "vortexgin_youtube_analytics_oauth_callback"