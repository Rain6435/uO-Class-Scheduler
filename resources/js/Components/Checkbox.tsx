import React from 'react';

// Extend the standard HTML input props
interface CheckboxProps extends React.InputHTMLAttributes<HTMLInputElement> {
    className?: string;
}

const Checkbox: React.FC<CheckboxProps> = ({ 
    className = '', 
    ...props 
}) => {
    return (
        <input
            {...props}
            type="checkbox"
            className={
                'rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 ' +
                className
            }
        />
    );
};

export default Checkbox;