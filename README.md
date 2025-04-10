<p align="center"><a href="https://team-nifty.com" target="_blank"><img src="https://user-images.githubusercontent.com/40495041/160839207-0e1593e0-ff3d-4407-b9d2-d3513c366ab9.svg" width="400"></a></p>

### 1. Installation

Remove the welcome route from `routes/web.php`.

Add the following to your `config/filesystem.php` config file

```php
    'links' => [
        ...
        public_path('flux') => base_path('vendor/team-nifty-gmbh/flux-erp/public'),
    ],
```

link the flux-erp assets

```bash
php artisan storage:link
```

This will create a symlink in `public/flux` to `vendor/team-nifty-gmbh/flux/public` which is where the flux-erp assets are stored.

If you want to use seeders add the following to your DatabaseSeeder.php file:

```php
$this->call(\FluxErp\Database\Seeders\FluxSeeder::class);
```

Because vite includes the pusher data into the build process its neccessary to rebuild the assets after the installation.

```bash
vite build
```

Please keep in mind to do so after setting the pusher credentials in the .env file.

### 2. Development

If you want to develop for flux-erp you should publish the docker files (this runs nginx instead of artisan serve)

```bash
php artisan vendor:publish --tag="flux-docker"
```

Alternative you can change your docker-compose.yml file to use the flux-erp docker files from the vendor folder.

```yaml
    laravel.test:
        build:
            context: ./vendor/team-nifty-gmbh/flux-erp/docker/8.2 # <--- Here
   ...
```

If you already have built the docker images you should rebuild them

```bash
sail build --no-cache
```

### 3. Running tests

```bash
cd vendor/flux-erp
composer i
composer test
```

# 2. Websockets

I expect you to run your flux application in nginx with certbot ssl.
Its important to understand that nginx serves as a proxy for the websockets running with supervisor.

This means that your supervisor config file should use a different port than the one you use for your nginx config.
You should build the Pusher config with port 443 as it should be the production port for your app.

```js
// resources/js/bootstrap.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    wsHost: window.location.hostname, // <-- important if you dont build the js file on the prod server
    wsPort: 80, // <-- this ensures that nginx will receive the request
    wssPort: 443, // <-- this ensures that nginx will receive the request
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
});
```

Your nginx config should look like this

```nginx
# Virtual Host configuration for tnconnect
#
# You can move that to a different file under sites-available/ and symlink that
# to sites-enabled/ to enable it.
#

map $http_upgrade $type {
    default "web";
    websocket "wss";
}

server {

    # SSL configuration
    #
    # listen 443 ssl default_server;
    # listen [::]:443 ssl default_server;
    #
    # Note: You should disable gzip for SSL traffic.
    # See: https://bugs.debian.org/773332
    #
    # Read up on ssl_ciphers to ensure a secure configuration.
    # See: https://bugs.debian.org/765782
    #
    # Self signed certs generated by the ssl-cert package
    # Don't use them in a production server!
    #
    # include snippets/snakeoil.conf;

    root /var/www/your.domain.com/public;

    charset utf-8;

    error_page 404 /index.php;

    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Frame-Options "SAMEORIGIN";

    # Add index.php to the list if you are using PHP
    index index.php;
    server_name your.domain.com;

    location / {
        try_files /nonexistent @$type;
    }

    location @web {
        # First attempt to serve request as file, then
        # as directory, then fall back to displaying a 404.
        try_files $uri $uri/ /index.php?$args;
    }

    location @wss {
        proxy_pass http://127.0.0.1:6001;
        proxy_set_header Host $host;
        proxy_read_timeout 60;
        proxy_connect_timeout 60;
        proxy_redirect off;

        # Allow the use of websockets
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }

    # pass PHP scripts to FastCGI server
    #
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    # deny access to .htaccess files, if Apache's document root
    # concurs with nginx's one
    #
    location ~ /\.ht {
        deny all;
    }

    ssl on;
    listen [::]:443 ssl ipv6only=on; # managed by Certbot
    listen 443 ssl; # managed by Certbot
    ssl_certificate /etc/letsencrypt/live/your.domain.com/fullchain.pem; # managed by Certbot
    ssl_certificate_key /etc/letsencrypt/live/your.domain.com/privkey.pem; # managed by Certbot
    include /etc/letsencrypt/options-ssl-nginx.conf; # managed by Certbot
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem; # managed by Certbot

}

server {
    if ($host = your.domain.com) {
        return 301 https://$host$request_uri;
    } # managed by Certbot


    listen 80 default_server;
    listen [::]:80 default_server;
    server_name your.domain.com;
    return 404; # managed by Certbot
}
```

Your .env file should look something like this:

```dotenv
# .env
REVERB_APP_ID=local
REVERB_APP_KEY=local
REVERB_APP_SECRET=local
REVERB_HOST=your.domain.com
REVERB_SCHEME=https
REVERB_PORT=443
```

This ensures that nginx handles your request, if you have mutliple instances of websockets running on the same server nginx will handle the request to the correct instance.

If you have only one instance of websockets running you can use the default port 6001 and remove the `PUSHER_PORT` from your .env file.

```dotenv
# .env
REVERB_APP_ID=local
REVERB_APP_KEY=local
REVERB_APP_SECRET=local
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=http
```

This will not be piped through nginx and will be handled by the websocket server directly.
