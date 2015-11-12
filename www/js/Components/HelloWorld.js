var React = require('react');

class HelloWorld extends React.Component {

  static propTypes = {};
  static defaultProps = {};

  constructor(props) {
    super(props);

    this.state = {};
  }

  render() {
    return (
      <h1>
        Hello, world!
      </h1>
    );
  }
};

export default HelloWorld;
