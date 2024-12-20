import React, { forwardRef, useEffect, useImperativeHandle, useRef } from 'react';

interface TextInputProps extends React.InputHTMLAttributes<HTMLInputElement> {
    type?: string;
    className?: string;
    isFocused?: boolean;
}

interface TextInputRef {
    focus: () => void;
}

const TextInput = forwardRef<TextInputRef, TextInputProps>(({
    type = 'text',
    className = '',
    isFocused = false,
    ...props
}, ref) => {
    const localRef = useRef<HTMLInputElement>(null);

    useImperativeHandle(ref, () => ({
        focus: () => localRef.current?.focus(),
    }));

    useEffect(() => {
        if (isFocused) {
            localRef.current?.focus();
        }
    }, [isFocused]);

    return (
        <input
            {...props}
            type={type}
            className={
                'rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 ' +
                className
            }
            ref={localRef}
        />
    );
});

// Add display name for debugging purposes
TextInput.displayName = 'TextInput';

export default TextInput;