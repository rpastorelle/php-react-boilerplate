# php-react-boilerplate
A boilerplate for PHP/ReactJS projects

## Environment Setup

**You must have PHP installed locally.**

1. Create env file: `cp .env.example .env`
2. Run composer: `composer install`
3. Install node dependencies: `npm install`

## Run Development Site

1. Start Dev server: `npm run up`
2. Visit [http://localhost:8080](http://localhost:8080), you should see something


## JS Build

**You must have [Browserify](http://browserify.org/) installed: `npm install -g browserify`**

1. Build the common JS: `npm run common`
2. Build the JS bundle: `npm run build`
3. Watch the bundle: `npm run watch`
