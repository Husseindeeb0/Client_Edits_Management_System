import { Menu } from 'lucide-react';

interface TopbarProps {
  onMenuClick: () => void;
}

/** Phones only: the button that opens the sidebar menu. On desktop the sidebar is always visible. */
export default function Topbar({ onMenuClick }: TopbarProps) {
  return (
    <header className="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-800 bg-slate-900 px-4 md:hidden">
      <button
        type="button"
        onClick={onMenuClick}
        className="rounded-md p-1 text-slate-400 hover:bg-slate-800"
        aria-label="Open menu"
      >
        <Menu className="size-5" />
      </button>
      <span className="text-base font-semibold">Client Edits</span>
    </header>
  );
}
