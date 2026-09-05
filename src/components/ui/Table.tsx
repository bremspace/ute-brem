import React from 'react';

export interface TableProps extends React.HTMLAttributes<HTMLDivElement> {
  /** Container wrapping the table — adds rounded-2xl, border, shadow */
  children: React.ReactNode;
}

/** Wrapper that adds the card chrome around a table */
export const TableContainer: React.FC<TableProps> = ({ className = '', children }) => (
  <div className={`bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden ${className}`}>
    {children}
  </div>
);

/** Table title bar — shown above the table inside TableContainer */
export interface TableHeaderProps extends React.HTMLAttributes<HTMLDivElement> {
  icon?: React.ReactNode;
  count?: number;
  extra?: React.ReactNode;
}

export const TableHeader: React.FC<TableHeaderProps> = ({
  icon,
  count,
  extra,
  className = '',
  children,
}) => (
  <div className={`px-5 py-3.5 border-b border-slate-100 flex items-center justify-between ${className}`}>
    <div className="flex items-center gap-2 font-bold text-slate-800 text-sm">
      {icon}
      {children}
      {count !== undefined && (
        <span className="text-[10px] font-semibold bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">
          {count}
        </span>
      )}
    </div>
    {extra && <div>{extra}</div>}
  </div>
);

export interface ColumnDef {
  /** Column header text */
  header: string;
  /** Tailwind alignment class for the th — default '' (left) */
  align?: 'left' | 'center' | 'right';
  /** Tailwind class on the th element */
  className?: string;
}

export interface TableBaseProps {
  columns: ColumnDef[];
  /** Text shown when data is empty */
  emptyMessage?: string;
  /** Icon shown when data is empty */
  emptyIcon?: React.ReactNode;
  /** Number of columns for colSpan in empty row */
  colSpan?: number;
  children: React.ReactNode;
  /** Additional tbody classes */
  tbodyClassName?: string;
}

/** Semantic table that matches the existing POS table pattern */
export const TableBase: React.FC<TableBaseProps> = ({
  columns,
  emptyMessage = 'Tidak ada data',
  emptyIcon,
  colSpan,
  children,
  tbodyClassName = '',
}) => (
  <div className="overflow-x-auto">
    <table className="w-full text-left text-xs">
      <thead className="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-100">
        <tr>
          {columns.map((col, i) => (
            <th
              key={i}
              className={`px-4 py-3 ${col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : ''} ${col.className || ''}`}
            >
              {col.header}
            </th>
          ))}
        </tr>
      </thead>
      <tbody className={`divide-y divide-slate-100 text-slate-700 ${tbodyClassName}`}>
        {children}
      </tbody>
    </table>
  </div>
);

/** Row component with hover styling */
export const TableRow: React.FC<React.HTMLAttributes<HTMLTableRowElement>> = ({
  className = '',
  children,
  ...props
}) => (
  <tr className={`hover:bg-slate-50/70 transition-colors ${className}`} {...props}>
    {children}
  </tr>
);

/** Empty state row */
export const TableEmpty: React.FC<{
  colSpan: number;
  message?: string;
  icon?: React.ReactNode;
}> = ({ colSpan, message = 'Tidak ada data', icon }) => (
  <tr>
    <td colSpan={colSpan} className="px-5 py-12 text-center text-slate-400">
      {icon}
      <p className="text-xs font-semibold mt-2">{message}</p>
    </td>
  </tr>
);
