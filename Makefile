deploy:
	ssh o2switch 'cd ~/sites/paxevent.com && git pull origin main && make install'


install: vendor/autoload.php .env  public/storage public/build/manifest.json
	php artisan config:cache
	php artisan route:cache
	php artisan view:cache
	php artisan migrate --force

.env:
	cp .env.example .env
	php artisan key:generate


vendor/autoload.php: composer.lock
	composer install 
	touch vendor/autoload.php	


public/storage: 	
	php artisan storage:link

public/build/manifest.json: package.json
	npm install
	npm run build
