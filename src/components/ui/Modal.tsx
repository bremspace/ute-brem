import React, { useEffect, useCallback } from 'react';
import { X } from 'lucide-react';

export interface ModalProps {
  open: boolean;
  onClose: () => void;
  /** Max width of the modal — defaults to 'lg' (max-w-lg) */
  size?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
  /** Dark header variant (used by PaymentModal/CashSessionModal) */
  darkHeader?: boolean;
  /** Title shown in header */
  title?: string;
  /** Subtitle under title */
  subtitle?: string;
  /** Icon shown next to title in dark header mode */
  headerIcon?: React.ReactNode;
  /** Hide the default close button */
  hideCloseButton?: boolean;
  /** Header content — overrides title/subtitle/icon */
  header?: React.ReactNode;
  /** Footer content — rendered in a sticky footer bar */
  footer?: React.ReactNode;
  /** Disable closing on overlay click */
  disableOverlayClose?: boolean;
  /** Additional classes for the body scroll area */
  bodyClassName?: string;
  children: React.ReactNode;
}

const sizeClasses: Record<string, string> = {
  sm: 'max-w-sm',
  md: 'max-w-md',
  lg: 'max-w-lg',
  xl: 'max-w-2xl',
  '2xl': 'max-w-4xl',
};

export const Modal: React.FC<ModalProps> = ({
  open,
  onClose,
  size = 'lg',
  darkHeader = false,
  title,
  subtitle,
  headerIcon,
  hideCloseButton = false,
  header,
  footer,
  disableOverlayClose = false,
  bodyClassName = '',
  children,
}) => {
  const handleKeyDown = useCallback(
    (e: KeyboardEvent) => {
      if (e.key === 'Escape' && !disableOverlayClose) onClose();
    },
    [onClose, disableOverlayClose],
  );

  useEffect(() => {
    if (open) {
      document.addEventListener('keydown', handleKeyDown);
      document.body.style.overflow = 'hidden';
    }
    return () => {
      document.removeEventListener('keydown', handleKeyDown);
      document.body.style.overflow = '';
    };
  }, [open, handleKeyDown]);

  if (!open) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in">
      {/* Overlay */}
      <div
        className="fixed inset-0"
        onClick={disableOverlayClose ? undefined : onClose}
      />

      {/* Modal card */}
      <div
        className={`relative bg-white w-full ${sizeClasses[size]} rounded-2xl shadow-2xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh] animate-scale-in`}
      >
        {/* Dark header */}
        {darkHeader && (header || title) && (
          <div className="px-6 py-4 bg-slate-900 text-white flex items-center justify-between flex-shrink-0">
            {header ?? (
              <div className="flex items-center gap-3">
                {headerIcon && (
                  <div className="w-9 h-9 rounded-xl bg-primary-600/30 border border-primary-500/40 flex items-center justify-center text-primary-400">
                    {headerIcon}
                  </div>
                )}
                <div>
                  {title && <h3 className="font-bold text-base">{title}</h3>}
                  {subtitle && <p className="text-xs text-slate-400 mt-0.5">{subtitle}</p>}
                </div>
              </div>
            )}
            {!hideCloseButton && (
              <button
                onClick={onClose}
                className="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
              >
                <X className="w-5 h-5" />
              </button>
            )}
          </div>
        )}

        {/* Light header (default) */}
        {!darkHeader && (header || title) && (
          <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
            {header ?? (
              <div>
                {title && <h3 className="text-sm font-black text-slate-900">{title}</h3>}
                {subtitle && <p className="text-[11px] text-slate-500 mt-0.5">{subtitle}</p>}
              </div>
            )}
            {!hideCloseButton && (
              <button
                onClick={onClose}
                className="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
              >
                <X className="w-4 h-4" />
              </button>
            )}
          </div>
        )}

        {/* Body */}
        <div className={`flex-1 overflow-y-auto p-6 ${bodyClassName}`}>
          {children}
        </div>

        {/* Footer */}
        {footer && (
          <div className="sticky bottom-0 bg-white border-t border-slate-100 px-6 py-4 flex items-center justify-end gap-2 flex-shrink-0">
            {footer}
          </div>
        )}
      </div>
    </div>
  );
};
