import React from 'react';
import { Search } from 'lucide-react';

export interface SearchInputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  /** Override the default search icon */
  icon?: React.ReactNode;
}

export const SearchInput = React.forwardRef<HTMLInputElement, SearchInputProps>(
  ({ icon, className = '', ...props }, ref) => {
    return (
      <div className="relative w-full">
        <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
          {icon ?? <Search className="w-4 h-4" />}
        </div>
        <input
          ref={ref}
          type="text"
          className={`w-full pl-10 pr-4 py-2 bg-white border border-slate-300 rounded-xl
            text-xs font-semibold text-slate-900
            focus:outline-none focus:ring-2 focus:ring-primary-500
            shadow-sm transition-shadow
            ${className}`}
          {...props}
        />
      </div>
    );
  },
);

SearchInput.displayName = 'SearchInput';
