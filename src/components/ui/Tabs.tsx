import React from 'react';

export interface TabItem {
  id: string;
  label: string;
  icon?: React.ReactNode;
}

export interface TabsProps {
  /** Available tab definitions */
  tabs: TabItem[];
  /** Currently active tab ID */
  activeTab: string;
  /** Callback when tab changes */
  onChange: (tabId: string) => void;
  /** Visual variant — 'pill' matches POS filter pills, 'segmented' matches CustomersView tab bar */
  variant?: 'pill' | 'segmented';
  className?: string;
}

/** Pill-style tabs: active = bg-primary-600 text-white, inactive = bg-white border border-slate-200 */
const PillTabs: React.FC<TabsProps> = ({ tabs, activeTab, onChange, className = '' }) => (
  <div className={`flex items-center gap-2 overflow-x-auto pb-1 text-xs ${className}`}>
    {tabs.map(tab => (
      <button
        key={tab.id}
        onClick={() => onChange(tab.id)}
        className={`px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap transition-all flex items-center gap-1.5 ${
          activeTab === tab.id
            ? 'bg-primary-600 text-white shadow-sm'
            : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100'
        }`}
      >
        {tab.icon}
        {tab.label}
      </button>
    ))}
  </div>
);

/** Segmented tabs: active = bg-white shadow-sm text-primary-700, in bg-slate-100 container */
const SegmentedTabs: React.FC<TabsProps> = ({ tabs, activeTab, onChange, className = '' }) => (
  <div className={`flex gap-1 bg-slate-100 rounded-xl p-1 w-fit ${className}`}>
    {tabs.map(tab => (
      <button
        key={tab.id}
        onClick={() => onChange(tab.id)}
        className={`px-4 py-2 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5 ${
          activeTab === tab.id
            ? 'bg-white shadow-sm text-primary-700'
            : 'text-slate-500 hover:text-slate-700'
        }`}
      >
        {tab.icon}
        {tab.label}
      </button>
    ))}
  </div>
);

export const Tabs: React.FC<TabsProps> = ({ variant = 'pill', ...props }) => {
  return variant === 'pill' ? <PillTabs {...props} /> : <SegmentedTabs {...props} />;
};
