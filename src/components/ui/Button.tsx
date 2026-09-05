import React from 'react';

type ButtonVariant = 'primary' | 'secondary' | 'danger' | 'ghost' | 'outline' | 'success';
type ButtonSize = 'xs' | 'sm' | 'md' | 'lg';

export interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: ButtonVariant;
  size?: ButtonSize;
  icon?: React.ReactNode;
  loading?: boolean;
}

const variantClasses: Record<ButtonVariant, string> = {
  primary:
    'bg-primary-600 hover:bg-primary-700 text-white font-bold shadow-md shadow-primary-600/30 active:bg-primary-800',
  secondary:
    'bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold',
  danger:
    'bg-red-600 hover:bg-red-700 text-white font-bold shadow-md shadow-red-500/30 active:bg-red-800',
  ghost:
    'bg-transparent hover:bg-slate-100 text-slate-600 font-semibold',
  outline:
    'bg-transparent border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold',
  success:
    'bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md shadow-emerald-600/30 active:bg-emerald-800',
};

const sizeClasses: Record<ButtonSize, string> = {
  xs: 'px-2.5 py-1.5 text-2xs',
  sm: 'px-3 py-2 text-xs',
  md: 'px-4 py-2.5 text-xs',
  lg: 'px-5 py-3 text-sm',
};

export const Button: React.FC<ButtonProps> = ({
  variant = 'primary',
  size = 'md',
  icon,
  loading,
  disabled,
  className = '',
  children,
  ...props
}) => {
  return (
    <button
      disabled={disabled || loading}
      className={`inline-flex items-center justify-center gap-1.5 rounded-xl transition-all
        disabled:opacity-50 disabled:cursor-not-allowed
        ${variantClasses[variant]} ${sizeClasses[size]} ${className}`}
      {...props}
    >
      {loading ? (
        <svg className="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
      ) : icon ? (
        <span className="flex-shrink-0">{icon}</span>
      ) : null}
      {children}
    </button>
  );
};
