import React from 'react';
import ReactDOM from 'react-dom';

function Main() {
    return (
        <div className="container">
            Tms Server
        </div>
    );
}

export default Main;

if (document.getElementById('main')) {
    ReactDOM.render(<Main />, document.getElementById('main'));
}
