import { Fragment } from 'react';
import { ChevronRight } from 'lucide-react';
import { Link } from 'react-router';

interface Crumb {
  label: string;
  /** What the crumb is, e.g. "Company"; shown above the name and on hover. */
  type?: string;
  /** Leave out for the current page (the last crumb). */
  to?: string;
}

/** E.g. Clients › Acme › Acme Corp › Website redesign, each name labeled with what it is */
export default function Breadcrumbs({ items }: { items: Crumb[] }) {
  return (
    <nav aria-label="Breadcrumb" className="flex flex-wrap items-end gap-x-2 gap-y-3 text-sm text-slate-400">
      {items.map((item, index) => (
        <Fragment key={`${item.label}-${index}`}>
          {index > 0 && <ChevronRight className="mb-0.5 size-4 shrink-0" />}

          <div className="flex flex-col" title={item.type ? `${item.type}: ${item.label}` : undefined}>
            {item.type && <span className="text-xs font-medium tracking-wide text-teal-400 uppercase">{item.type}</span>}
            {item.to ? (
              <Link to={item.to} className="hover:text-white hover:underline">
                {item.label}
              </Link>
            ) : (
              <span className="font-medium text-slate-100">{item.label}</span>
            )}
          </div>
        </Fragment>
      ))}
    </nav>
  );
}
