import { Building, FolderKanban, LogOut, Paperclip, PencilLine, Users, X } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { NavLink } from 'react-router';
import { useLogoutMutation } from '../../client/authApi';
import { useAuth } from '../../hooks/useAuth';

interface NavItem {
  to: string;
  label: string;
  icon: LucideIcon;
}

const NAV_ITEMS: NavItem[] = [
  { to: '/clients', label: 'Clients', icon: Users },
  { to: '/companies', label: 'Companies', icon: Building },
  { to: '/projects', label: 'Projects', icon: FolderKanban },
  { to: '/edits', label: 'Edits', icon: PencilLine },
  { to: '/attachments', label: 'Attachments', icon: Paperclip },
];

interface SidebarProps {
  /** Only used below the `md` breakpoint, where the sidebar is a slide-in menu. */
  isOpen: boolean;
  onClose: () => void;
}

export default function Sidebar({ isOpen, onClose }: SidebarProps) {
  const { user } = useAuth();
  // Logging out ends the session; ProtectedRoute then redirects to /login
  const [logout, { isLoading }] = useLogoutMutation();

  return (
    <>
      {/* Backdrop for the mobile menu */}
      {isOpen && (
        <div className="fixed inset-0 z-30 bg-black/60 md:hidden" onClick={onClose} aria-hidden="true" />
      )}

      {/* Mobile: slide-in menu. Desktop: sticky, so it stays in place while the page scrolls */}
      <aside
        className={`fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 flex-col border-r border-slate-800 bg-slate-900 transition-transform md:sticky md:top-0 md:h-screen md:translate-x-0 ${
          isOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
      >
        <div className="flex h-16 items-center justify-between px-5">
          <span className="text-base font-semibold">Client Edits</span>
          <button
            type="button"
            onClick={onClose}
            className="rounded-md p-1 text-slate-400 hover:bg-slate-800 md:hidden"
            aria-label="Close menu"
          >
            <X className="size-5" />
          </button>
        </div>

        <nav className="flex flex-1 flex-col gap-1 overflow-y-auto px-3 py-2">
          {NAV_ITEMS.map(({ to, label, icon: Icon }) => (
            <NavLink
              key={to}
              to={to}
              onClick={onClose}
              className={({ isActive }) =>
                `flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium ${
                  isActive ? 'bg-teal-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white'
                }`
              }
            >
              <Icon className="size-4" />
              {label}
            </NavLink>
          ))}
        </nav>

        <div className="flex items-center justify-between gap-3 border-t border-slate-800 px-5 py-4">
          <span className="truncate text-sm text-slate-300">{user?.name}</span>
          <button
            type="button"
            onClick={() => logout()}
            disabled={isLoading}
            className="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-white disabled:opacity-50"
          >
            <LogOut className="size-4" />
            Log out
          </button>
        </div>
      </aside>
    </>
  );
}
