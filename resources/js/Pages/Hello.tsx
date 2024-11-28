import React from 'react';
import { Head } from '@inertiajs/react';

interface HelloWorldProps {
    name: string;
    className?: string;
  }
  const HelloWorld: React.FC<HelloWorldProps> = ({ name, className }) => {
    return <div className={className}>Hello, {name}!</div>;
  };
  const Hello: React.FC = () => {
    return (
      <div className="flex justify-center items-center min-h-screen">
        <Head>
          <title>Hello Inertia</title>
        </Head>
        <HelloWorld className='text-center' name="Inertia.js" />
      </div>
    );
  };
  export default Hello;