import React from 'react';
import { AlertCircle, CheckCircle, AlertTriangle, Info, X } from 'lucide-react';

type AlertVariant = 'info' | 'success' | 'warning' | 'error';

export interface AlertProps extends React.HTMLAttributes<HTMLDivElement> {
  variant?: AlertVariant;
  /** Title line */
  title?: string;
  /** Show a close button */
  dismissible?: boolean;
  onDismiss?: () => void;
  /** Icon override */
  icon?: React.ReactNode;
}

const variantConfig: Record<AlertVariant, { container: string; icon: string; defaultIcon: React.ReactNode }> = {
  info: {
    container: 'bg-info-50 border-info-200 text-info-900',
    icon: 'text-info-600',
    defaultIcon: <Info className="w-5 h-5" />,
  },
  success: {
    container: 'bg-success-50 border-success-200 text-success-700',
    icon: 'text-success-600',
    defaultIcon: <CheckCircle className="w-5 h-5" />,
  },
  warning: {
    container: 'bg-warning-50 border-warning-200 text-warning-700',
    icon: 'text-warning-600',
    defaultIcon: <AlertTriangle className="w-5 h-5" />,
  },
  error: {
    container: 'bg-danger-50 border-danger-200 text-danger-700',
    icon: 'text-danger-600',
    defaultIcon: <AlertCircle className="w-5 h-5" />,
  },
};

export const Alert: React.FC<AlertProps> = ({
  variant = 'info',
  title,
  dismissible = false,
  onDismiss,
  icon,
  className = '',
  children,
}) => {
  const config = variantConfig[variant];
  const displayIcon = icon ?? config.defaultIcon;

  return (
    <div
      className={`p-4 rounded-xl border flex items-start gap-3 ${config.container} ${className}`}
      role="alert"
    >
      <span className={`flex-shrink-0 mt-0.5 ${config.icon}`}>
        {displayIcon}
      </span>
      <div className="flex-1 text-xs leading-relaxed">
        {title && <p className="font-bold mb-0.5">{title}</p>}
        {children}
      </div>
      {dismissible && onDismiss && (
        <button
          onClick={onDismiss}
          className={`flex-shrink-0 p-0.5 rounded-lg hover:bg-black/5 transition-colors ${config.icon}`}
        >
          <X className="w-4 h-4" />
        </button>
      )}
    </div>
  );
};
