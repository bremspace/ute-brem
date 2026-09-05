import React from 'react';

type StatColor = 'primary' | 'success' | 'warning' | 'danger' | 'info' | 'violet';

export interface StatCardProps {
  /** The metric value displayed large */
  value: React.ReactNode;
  /** Label / description */
  label: string;
  /** Optional secondary description under the value */
  description?: string;
  /** Icon rendered in the badge */
  icon: React.ReactNode;
  /** Color theme for the icon badge and value text */
  color?: StatColor;
  /** Extra content below the main value */
  children?: React.ReactNode;
  className?: string;
}

const colorMap: Record<StatColor, { badge: string; value: string }> = {
  primary: { badge: 'bg-primary-50 text-primary-600', value: 'text-primary-700' },
  success: { badge: 'bg-emerald-50 text-emerald-600', value: 'text-emerald-700' },
  warning: { badge: 'bg-amber-50 text-amber-600', value: 'text-amber-600' },
  danger: { badge: 'bg-red-50 text-red-600', value: 'text-red-600' },
  info: { badge: 'bg-blue-50 text-blue-600', value: 'text-blue-600' },
  violet: { badge: 'bg-violet-50 text-violet-600', value: 'text-violet-600' },
};

export const StatCard: React.FC<StatCardProps> = ({
  value,
  label,
  description,
  icon,
  color = 'primary',
  children,
  className = '',
}) => {
  const colors = colorMap[color];

  return (
    <div
      className={`bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col justify-between ${className}`}
    >
      <div className="flex items-center justify-between">
        <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">
          {label}
        </span>
        <div
          className={`w-8 h-8 rounded-lg flex items-center justify-center font-bold ${colors.badge}`}
        >
          {icon}
        </div>
      </div>
      <div className="mt-3">
        <div className={`text-2xl font-black ${colors.value}`}>
          {value}
        </div>
        {description && (
          <div className="text-xs text-slate-500 mt-1">{description}</div>
        )}
        {children}
      </div>
    </div>
  );
};
