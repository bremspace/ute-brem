import React from 'react';

export interface SelectOption {
  value: string | number;
  label: string;
}

export interface SelectProps extends Omit<React.SelectHTMLAttributes<HTMLSelectElement>, 'children'> {
  /** Label shown above the select */
  label?: string;
  /** Show required asterisk */
  required?: boolean;
  /** Error message */
  error?: string;
  /** Options array */
  options: SelectOption[];
  /** Placeholder for the first empty option */
  placeholder?: string;
  /** Extra classes for the wrapping div (defaults to w-full) */
  wrapperClassName?: string;
}

export const Select = React.forwardRef<HTMLSelectElement, SelectProps>(
  ({ label, required, error, options, placeholder, className = '', wrapperClassName = 'w-full', ...props }, ref) => {
    return (
      <div className={wrapperClassName}>
        {label && (
          <label className="block text-[10px] font-bold text-slate-500 uppercase mb-1">
            {label}
            {required && <span className="text-red-500 ml-0.5">*</span>}
          </label>
        )}
        <select
          ref={ref}
          className={`w-full px-3 py-2 bg-slate-50 border rounded-xl text-xs font-semibold text-slate-700
            focus:outline-none focus:ring-2 focus:ring-primary-500 transition-shadow
            ${error ? 'border-red-300 focus:ring-red-500' : 'border-slate-200'}
            ${className}`}
          {...props}
        >
          {placeholder && (
            <option value="">{placeholder}</option>
          )}
          {options.map(opt => (
            <option key={opt.value} value={opt.value}>
              {opt.label}
            </option>
          ))}
        </select>
        {error && (
          <p className="text-[10px] text-red-500 mt-1 font-semibold">{error}</p>
        )}
      </div>
    );
  },
);

Select.displayName = 'Select';
