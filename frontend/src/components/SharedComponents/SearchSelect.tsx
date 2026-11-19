import { useEffect, useRef, useState } from 'react';
import { ChevronDown, ChevronLeft, ChevronRight, LoaderCircle } from 'lucide-react';
import SearchInput from './SearchInput';

export interface SelectOption {
  id: number;
  label: string;
  /** Extra detail shown next to the label, e.g. the client of a company. */
  hint?: string | null;
}

interface SearchSelectProps {
  /** The button's id, so the form label can point to it. */
  id: string;
  placeholder: string;
  selectedId: number | null;
  selectedLabel?: string;
  /** The current page of results only, not the whole list. */
  options: SelectOption[];
  isLoading: boolean;
  page: number;
  lastPage: number;
  onPageChange: (page: number) => void;
  onSearch: (term: string) => void;
  onSelect: (option: SelectOption) => void;
}

/**
 * A dropdown with a search box and paginated results, for choosing one record
 * out of many. The data comes from the parent (ClientSelect, CompanySelect, ...).
 */
export default function SearchSelect({
  id,
  placeholder,
  selectedId,
  selectedLabel,
  options,
  isLoading,
  page,
  lastPage,
  onPageChange,
  onSearch,
  onSelect,
}: SearchSelectProps) {
  const [isOpen, setIsOpen] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);

  function close() {
    setIsOpen(false);
    onSearch(''); // the next time it opens, it starts from the full first page
  }

  // Close when clicking anywhere outside the dropdown
  useEffect(() => {
    if (!isOpen) {
      return;
    }

    function handleClickOutside(event: MouseEvent) {
      if (!containerRef.current?.contains(event.target as Node)) {
        close();
      }
    }

    document.addEventListener('mousedown', handleClickOutside);

    return () => document.removeEventListener('mousedown', handleClickOutside);
  });

  return (
    <div ref={containerRef}>
      <button
        id={id}
        type="button"
        onClick={() => (isOpen ? close() : setIsOpen(true))}
        aria-expanded={isOpen}
        className="form-input flex items-center justify-between gap-2 text-left"
      >
        <span className={`truncate ${selectedLabel ? 'text-slate-100' : 'text-slate-500'}`}>
          {selectedLabel ?? placeholder}
        </span>
        <ChevronDown className="size-4 shrink-0 text-slate-400" />
      </button>

      {isOpen && (
        <div className="mt-1 space-y-2 rounded-md border border-slate-700 bg-slate-950 p-2">
          <SearchInput onSearch={onSearch} className="w-full" />

          {isLoading ? (
            <div className="flex justify-center py-4">
              <LoaderCircle className="size-5 animate-spin text-slate-500" aria-label="Loading" />
            </div>
          ) : options.length === 0 ? (
            <p className="py-4 text-center text-sm text-slate-500">No results.</p>
          ) : (
            <ul role="listbox" className="max-h-56 overflow-y-auto">
              {options.map((option) => (
                <li key={option.id}>
                  <button
                    type="button"
                    role="option"
                    aria-selected={option.id === selectedId}
                    onClick={() => {
                      onSelect(option);
                      close();
                    }}
                    className={`flex w-full items-baseline justify-between gap-3 rounded px-2 py-1.5 text-left text-sm hover:bg-slate-800 ${
                      option.id === selectedId ? 'bg-slate-800 text-teal-300' : 'text-slate-200'
                    }`}
                  >
                    <span className="truncate">{option.label}</span>
                    {option.hint && <span className="shrink-0 text-xs text-slate-500">{option.hint}</span>}
                  </button>
                </li>
              ))}
            </ul>
          )}

          {lastPage > 1 && (
            <div className="flex items-center justify-between border-t border-slate-800 pt-2 text-xs text-slate-400">
              <button
                type="button"
                onClick={() => onPageChange(page - 1)}
                disabled={page <= 1}
                className="rounded p-1 hover:bg-slate-800 disabled:opacity-40"
                aria-label="Previous page"
              >
                <ChevronLeft className="size-4" />
              </button>
              <span>
                Page {page} of {lastPage}
              </span>
              <button
                type="button"
                onClick={() => onPageChange(page + 1)}
                disabled={page >= lastPage}
                className="rounded p-1 hover:bg-slate-800 disabled:opacity-40"
                aria-label="Next page"
              >
                <ChevronRight className="size-4" />
              </button>
            </div>
          )}
        </div>
      )}
    </div>
  );
}
