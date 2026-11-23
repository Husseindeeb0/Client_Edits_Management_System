import { useEffect, useRef, useState } from 'react';
import type { KeyboardEvent } from 'react';

interface InlineDateInputProps {
  /** The saved date (`YYYY-MM-DD`), or `null` when there is none. */
  value: string | null;
  /** Read by screen readers, e.g. "Due date". */
  label: string;
  /** Saves the new date (`null` when cleared); resolves to `false` when saving failed. */
  onSave: (value: string | null) => Promise<boolean>;
}

// Typing a date fires a change on every digit, so wait a moment before saving
const SAVE_DELAY_MS = 700;

/**
 * A date input for table cells. The date is saved shortly after it is picked,
 * or right away on blur or Enter; Esc puts the saved date back.
 *
 * Give it `key={savedDate}` so it starts fresh once the saved date changes.
 */
export default function InlineDateInput({ value, label, onSave }: InlineDateInputProps) {
  // `null` = not being edited, show the saved date
  const [draft, setDraft] = useState<string | null>(null);
  const timer = useRef<number | undefined>(undefined);
  // The last date sent to the server, so the same date is never saved twice
  const lastSent = useRef(value ?? '');

  useEffect(() => () => window.clearTimeout(timer.current), []);

  async function save(next: string) {
    window.clearTimeout(timer.current);

    if (next === lastSent.current) {
      return;
    }

    lastSent.current = next;

    // Keep showing the new date while it saves; go back to the saved one if it fails
    if (!(await onSave(next || null))) {
      lastSent.current = value ?? '';
      setDraft(null);
    }
  }

  function handleChange(next: string) {
    setDraft(next);
    window.clearTimeout(timer.current);
    timer.current = window.setTimeout(() => save(next), SAVE_DELAY_MS);
  }

  function handleKeyDown(event: KeyboardEvent<HTMLInputElement>) {
    if (event.key === 'Enter') {
      event.currentTarget.blur();
    } else if (event.key === 'Escape') {
      window.clearTimeout(timer.current);
      setDraft(null);
    }
  }

  return (
    <input
      type="date"
      aria-label={label}
      value={draft ?? value ?? ''}
      onChange={(event) => handleChange(event.target.value)}
      onBlur={() => draft !== null && save(draft)}
      onKeyDown={handleKeyDown}
      className="form-input w-auto py-1"
    />
  );
}
