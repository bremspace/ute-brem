import React, { useState } from 'react';

interface PatternLockInputProps {
  value?: string;
  onChange: (value: string) => void;
}

export const PatternLockInput: React.FC<PatternLockInputProps> = ({ value = '', onChange }) => {
  const [selectedDots, setSelectedDots] = useState<number[]>(() => {
    if (!value) return [];
    return value.split('-').map(Number).filter(n => !isNaN(n));
  });

  const handleDotClick = (dotIndex: number) => {
    let next: number[];
    if (selectedDots.includes(dotIndex)) {
      // Toggle or remove from dotIndex
      const idx = selectedDots.indexOf(dotIndex);
      next = selectedDots.slice(0, idx);
    } else {
      next = [...selectedDots, dotIndex];
    }
    setSelectedDots(next);
    onChange(next.join('-'));
  };

  const handleClear = () => {
    setSelectedDots([]);
    onChange('');
  };

  return (
    <div className="flex flex-col items-center p-3 bg-slate-900 rounded-xl text-white">
      <div className="text-[11px] text-slate-400 mb-2">
        Klik titik secara berurutan untuk mencatat pola kunci:
      </div>

      {/* 3x3 Grid */}
      <div className="grid grid-cols-3 gap-5 p-3 bg-slate-950 rounded-xl border border-slate-800">
        {[1, 2, 3, 4, 5, 6, 7, 8, 9].map(num => {
          const isSelected = selectedDots.includes(num);
          const order = selectedDots.indexOf(num) + 1;

          return (
            <button
              key={num}
              type="button"
              onClick={() => handleDotClick(num)}
              className={`w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs transition-all relative ${
                isSelected
                  ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/50 scale-110'
                  : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white'
              }`}
            >
              {isSelected ? order : ''}
              {!isSelected && <div className="w-2.5 h-2.5 rounded-full bg-slate-500" />}
            </button>
          );
        })}
      </div>

      {/* Sequence readout & clear button */}
      <div className="flex items-center justify-between w-full mt-3 px-2">
        <div className="text-xs font-mono text-primary-400">
          Pola: {selectedDots.length > 0 ? selectedDots.join(' → ') : '(Belum ada)'}
        </div>
        {selectedDots.length > 0 && (
          <button
            type="button"
            onClick={handleClear}
            className="text-[11px] text-red-400 hover:text-red-300 font-semibold underline"
          >
            Reset Pola
          </button>
        )}
      </div>
    </div>
  );
};
