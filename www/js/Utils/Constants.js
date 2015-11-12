var ENV = (
    'dev'
    //'stage'
    //'production'
);

var isProd     = true,
    apiVersion = 'v1',
    apiHost    = 'http://api.phpreactboilerplate.com',
    siteRoot   = 'https://phpreactboilerplate.com';

switch (ENV) {
    case 'dev':
        isProd   = false;
        apiHost  = 'http://dev-api.phpreactboilerplate.com';
        siteRoot = 'https://phpreactboilerplate.com';
        break;
    case 'stage':
        isProd   = false;
        apiHost  = 'http://stage-api.phpreactboilerplate.com';
        siteRoot = 'https://phpreactboilerplate.com';
        break;
}

export var Config = {
  ENV: ENV,
  api_root: apiHost+'/'+apiVersion+'/',
  site_root: siteRoot+'/'
};
