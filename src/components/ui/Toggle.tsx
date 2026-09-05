import React from 'react';

export interface ToggleProps {
  checked: boolean;
  onChange: (checked: boolean) => void;
  /** Disable the toggle */
  disabled?: boolean;
  /** Label text next to the toggle */
  label?: string;
  className?: string;
}

export const Toggle: React.FC<ToggleProps> = ({
  checked,
  onChange,
  disabled = false,
  label,
  className = '',
}) => {
  return (
    <div className={`flex items-center gap-2 ${className}`}>
      <button
        type="button"
        role="switch"
        aria-checked={checked}
        disabled={disabled}
        onClick={() => onChange(!checked)}
        className={`relative w-9 h-5 rounded-full transition-colors
          ${checked ? 'bg-primary-600' : 'bg-slate-300'}
          ${disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}`}
      >
        <span
          className={`absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform
            ${checked ? 'left-[18px]' : 'left-0.5'}`}
        />
      </button>
      {label && (
        <span className="text-[11px] font-bold text-slate-600">{label}</span>
      )}
    </div>
  );
};
