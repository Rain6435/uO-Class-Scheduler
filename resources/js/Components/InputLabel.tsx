import React from 'react';

interface InputLabelProps extends React.LabelHTMLAttributes<HTMLLabelElement> {
    value?: string;
    className?: string;
}

const InputLabel: React.FC<InputLabelProps> = ({
    value,
    className = '',
    children,
    ...props
}) => {
    return (
        <label
            {...props}
            className={
                `block text-sm font-medium text-gray-700 ` +
                className
            }
        >
            {value ? value : children}
        </label>
    );
};


export default InputLabel;