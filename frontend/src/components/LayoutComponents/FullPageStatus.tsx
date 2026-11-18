import { LoaderCircle } from 'lucide-react';

interface FullPageStatusProps {
  /** When set, shows the error and a retry button instead of the spinner. */
  error?: string;
  onRetry?: () => void;
}

/** Shown while the session is being checked, or when that check failed. */
export default function FullPageStatus({ error, onRetry }: FullPageStatusProps) {
  return (
    <div className="flex min-h-screen items-center justify-center bg-slate-950 px-4">
      {error ? (
        <div className="max-w-sm text-center">
          <p className="text-sm text-slate-300">{error}</p>
          {onRetry && (
            <button
              type="button"
              onClick={onRetry}
              className="mt-4 rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-500"
            >
              Try again
            </button>
          )}
        </div>
      ) : (
        <LoaderCircle className="size-8 animate-spin text-slate-500" aria-label="Loading" />
      )}
    </div>
  );
}
