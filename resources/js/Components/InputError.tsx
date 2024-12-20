import React from 'react';

interface InputErrorProps extends React.HTMLAttributes<HTMLParagraphElement> {
    message?: string;
    className?: string;
}

const InputError: React.FC<InputErrorProps> = ({ 
    message, 
    className = '', 
    ...props 
}) => {
    return message ? (
        <p
            {...props}
            className={'text-sm text-red-600 ' + className}
        >
            {message}
        </p>
    ) : null;
};

export default InputError;