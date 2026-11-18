import { useEffect, useRef } from 'react';
import type { ChangeEvent } from 'react';

interface SearchInputProps {
  onSearch: (term: string) => void;
  placeholder?: string;
}

/** Calls `onSearch` 300 ms after the admin stops typing, instead of on every key press. */
export default function SearchInput({ onSearch, placeholder = 'Search…' }: SearchInputProps) {
  const timer = useRef<number | undefined>(undefined);

  // Don't call onSearch after the page has been left
  useEffect(() => () => clearTimeout(timer.current), []);

  function handleChange(event: ChangeEvent<HTMLInputElement>) {
    const term = event.target.value.trim();

    clearTimeout(timer.current);
    timer.current = window.setTimeout(() => onSearch(term), 300);
  }

  return (
    <input
      type="search"
      placeholder={placeholder}
      aria-label={placeholder}
      onChange={handleChange}
      className="form-input sm:max-w-xs"
    />
  );
}
