import React from 'react';

export interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  /** Label shown above the input */
  label?: string;
  /** Show required asterisk */
  required?: boolean;
  /** Error message — also adds red ring */
  error?: string;
  /** Left icon slot */
  leftIcon?: React.ReactNode;
  /** Right icon / element slot */
  rightSlot?: React.ReactNode;
  /** Wrapper div className (default: 'w-full') */
  wrapperClassName?: string;
}

export const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({ label, required, error, leftIcon, rightSlot, wrapperClassName = 'w-full', className = '', ...props }, ref) => {
    const hasIcon = !!leftIcon;

    return (
      <div className={wrapperClassName}>
        {label && (
          <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">
            {label}
            {required && <span className="text-red-500 ml-0.5">*</span>}
          </label>
        )}
        <div className="relative">
          {leftIcon && (
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
              {leftIcon}
            </div>
          )}
          <input
            ref={ref}
            className={`w-full px-3 py-2 bg-slate-50 border rounded-xl text-xs font-semibold text-slate-900
              focus:outline-none focus:ring-2 focus:ring-primary-500 transition-shadow
              ${hasIcon ? 'pl-9' : ''}
              ${rightSlot ? 'pr-10' : ''}
              ${error ? 'border-red-300 focus:ring-red-500' : 'border-slate-200'}
              ${props.disabled ? 'opacity-60 cursor-not-allowed' : ''}
              ${className}`}
            {...props}
          />
          {rightSlot && (
            <div className="absolute inset-y-0 right-0 pr-3 flex items-center">
              {rightSlot}
            </div>
          )}
        </div>
        {error && (
          <p className="text-[10px] text-red-500 mt-1 font-semibold">{error}</p>
        )}
      </div>
    );
  },
);

Input.displayName = 'Input';
