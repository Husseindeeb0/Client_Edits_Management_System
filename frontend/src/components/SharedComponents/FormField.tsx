import type { ReactNode } from 'react';

interface FormFieldProps {
  label: string;
  /** The id of the input inside, so clicking the label focuses it. */
  htmlFor: string;
  error?: string;
  children: ReactNode;
}

/** Label + input + the field's validation message. */
export default function FormField({ label, htmlFor, error, children }: FormFieldProps) {
  return (
    <div>
      <label htmlFor={htmlFor} className="mb-1 block text-sm font-medium text-slate-300">
        {label}
      </label>
      {children}
      {error && <p className="mt-1 text-sm text-red-400">{error}</p>}
    </div>
  );
}
