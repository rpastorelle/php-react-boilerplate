import React from 'react';
import ReactDOM from 'react-dom';
import path from 'path';
import Utils from '../Utils/Utils';
import HelloWorld from '../Components/HelloWorld';

export default class Routes {

  static homeRoute = 'home';

  static current(pathname) {
    var basename = path.basename(pathname) || Routes.homeRoute;
    var camelName = basename.replace(/-([a-z])/g, function (g) { return g[1].toUpperCase(); });
    if (typeof Routes[camelName] === 'function') {
      Routes[camelName]();
    }
  }

  //----------------------------
  // Custom Routes:
  //----------------------------

  static home() {
    ReactDOM.render(
      <HelloWorld />,
      document.getElementById('example')
    );
  }

}
