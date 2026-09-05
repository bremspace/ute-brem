import React from 'react';

/** Well-known status strings used across the app */
type AppStatus =
  // Generic
  | 'active' | 'inactive' | 'draft'
  // Sale / payment
  | 'paid' | 'void' | 'unpaid' | 'partial'
  // PO
  | 'ordered' | 'received' | 'completed' | 'cancelled'
  // Service
  | 'pending' | 'in_progress' | 'delivered'
  // Transfer
  | 'in_transit'
  // Member type
  | 'member' | 'regular'
  // Cash session
  | 'open' | 'closed'
  // Any other string
  | string;

export interface StatusBadgeProps extends React.HTMLAttributes<HTMLSpanElement> {
  status: AppStatus;
}

const statusColorMap: Record<string, string> = {
  active: 'bg-emerald-100 text-emerald-800',
  inactive: 'bg-slate-100 text-slate-500',
  paid: 'bg-emerald-100 text-emerald-800',
  completed: 'bg-emerald-100 text-emerald-800',
  delivered: 'bg-emerald-100 text-emerald-800',
  closed: 'bg-slate-100 text-slate-500',
  ordered: 'bg-blue-100 text-blue-800',
  in_transit: 'bg-blue-100 text-blue-800',
  in_progress: 'bg-blue-100 text-blue-800',
  pending: 'bg-amber-100 text-amber-800',
  partial: 'bg-amber-100 text-amber-800',
  open: 'bg-emerald-100 text-emerald-800',
  member: 'bg-amber-100 text-amber-800',
  draft: 'bg-slate-100 text-slate-600',
  cancelled: 'bg-red-100 text-red-800',
  void: 'bg-red-100 text-red-800',
  unpaid: 'bg-red-100 text-red-800',
  regular: 'bg-slate-100 text-slate-600',
};

export const StatusBadge: React.FC<StatusBadgeProps> = ({
  status,
  className = '',
  children,
  ...props
}) => {
  const colorClass = statusColorMap[status] || 'bg-slate-100 text-slate-600';
  const displayText = children || status.toUpperCase();

  return (
    <span
      className={`inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold ${colorClass} ${className}`}
      {...props}
    >
      {displayText}
    </span>
  );
};
