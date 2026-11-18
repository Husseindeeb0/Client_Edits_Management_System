import type { Paginated } from '../../types/api';

interface PaginationProps {
  meta: Paginated<unknown>['meta'];
  onPageChange: (page: number) => void;
}

export default function Pagination({ meta, onPageChange }: PaginationProps) {
  if (meta.total === 0) {
    return null;
  }

  return (
    <div className="flex flex-wrap items-center justify-between gap-3 py-3 text-sm text-slate-400">
      <span>
        Showing {meta.from}–{meta.to} of {meta.total}
      </span>

      <div className="flex items-center gap-2">
        <button
          type="button"
          className="btn-secondary"
          disabled={meta.current_page <= 1}
          onClick={() => onPageChange(meta.current_page - 1)}
        >
          Previous
        </button>
        <span>
          Page {meta.current_page} of {meta.last_page}
        </span>
        <button
          type="button"
          className="btn-secondary"
          disabled={meta.current_page >= meta.last_page}
          onClick={() => onPageChange(meta.current_page + 1)}
        >
          Next
        </button>
      </div>
    </div>
  );
}
