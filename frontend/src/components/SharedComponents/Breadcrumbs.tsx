import { Fragment } from 'react';
import { ChevronRight } from 'lucide-react';
import { Link } from 'react-router';

interface Crumb {
  label: string;
  /** Leave out for the current page (the last crumb). */
  to?: string;
}

/** E.g. Clients › Acme › Acme Corp › Website redesign */
export default function Breadcrumbs({ items }: { items: Crumb[] }) {
  return (
    <nav aria-label="Breadcrumb" className="flex flex-wrap items-center gap-1 text-sm text-slate-400">
      {items.map((item, index) => (
        <Fragment key={`${item.label}-${index}`}>
          {index > 0 && <ChevronRight className="size-4 shrink-0" />}
          {item.to ? (
            <Link to={item.to} className="hover:text-white hover:underline">
              {item.label}
            </Link>
          ) : (
            <span className="font-medium text-slate-100">{item.label}</span>
          )}
        </Fragment>
      ))}
    </nav>
  );
}
