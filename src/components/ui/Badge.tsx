import React from 'react';

type BadgeVariant = 'success' | 'warning' | 'danger' | 'info' | 'neutral' | 'primary' | 'amber';

export interface BadgeProps extends React.HTMLAttributes<HTMLSpanElement> {
  variant?: BadgeVariant;
  /** Render as a rounded-full pill */
  pill?: boolean;
}

const variantClasses: Record<BadgeVariant, string> = {
  success: 'bg-emerald-100 text-emerald-800',
  warning: 'bg-amber-100 text-amber-800',
  danger: 'bg-red-100 text-red-800',
  info: 'bg-blue-100 text-blue-800',
  neutral: 'bg-slate-100 text-slate-600',
  primary: 'bg-primary-100 text-primary-700',
  amber: 'bg-amber-100 text-amber-800',
};

export const Badge: React.FC<BadgeProps> = ({
  variant = 'neutral',
  pill = false,
  className = '',
  children,
  ...props
}) => {
  return (
    <span
      className={`inline-flex items-center px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide
        ${pill ? 'rounded-full' : 'rounded'}
        ${variantClasses[variant]} ${className}`}
      {...props}
    >
      {children}
    </span>
  );
};
