export default class Initialize {

  static globals() {
    // Expose globals like jQuery
    // window.jQuery = $;
  }

  static onLoad() {
    Initialize.globals();
  }
}
