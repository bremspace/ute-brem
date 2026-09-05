import React from 'react';

export interface CardProps extends React.HTMLAttributes<HTMLDivElement> {
  /** No padding, useful for overflow-hidden card wrappers */
  noPadding?: boolean;
  /** Interactive hover effect for clickable cards */
  interactive?: boolean;
}

export const Card: React.FC<CardProps> = ({
  noPadding = false,
  interactive = false,
  className = '',
  children,
  ...props
}) => {
  return (
    <div
      className={`bg-white rounded-2xl border border-slate-200/90 shadow-sm
        ${noPadding ? '' : 'p-5'}
        ${interactive ? 'hover:border-primary-300 hover:shadow-md cursor-pointer transition-all' : ''}
        ${className}`}
      {...props}
    >
      {children}
    </div>
  );
};

/** Card header bar with border-bottom — used inside Card for section headers */
export const CardHeader: React.FC<React.HTMLAttributes<HTMLDivElement>> = ({
  className = '',
  children,
  ...props
}) => {
  return (
    <div
      className={`px-6 py-4 border-b border-slate-100 flex items-center justify-between ${className}`}
      {...props}
    >
      {children}
    </div>
  );
};
