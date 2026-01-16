import React from 'react';
import axios from 'axios';

class Foo extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            fooItems: []
        }
    }

    componentDidMount() {
        this.getFooData();
    }

    getFooData() {
        axios.get('https://api.example.test/foo')
            .then((res) => {
               this.setFooItems(res.data.fooItems);
            });
    }

    setFooItems(items) {
        this.setState({
            fooItems: items,
        });
    }

    render() {
        const { fooItems } = this.state;

        return (
            <>
                <h1>Hello World!</h1>
                {fooItems.length > 0 && (
                    <ul>
                        {fooItems.map((item) => <li key={item.name}>{item.name}</li>)}
                    </ul>
                )}
            </>
        )
    }
}

export default Foo;
