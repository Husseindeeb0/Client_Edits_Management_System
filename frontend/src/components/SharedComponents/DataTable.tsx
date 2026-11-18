import type { ReactNode } from 'react';

export interface Column<T> {
  header: string;
  render: (row: T) => ReactNode;
}

interface DataTableProps<T> {
  columns: Column<T>[];
  rows: T[];
}

/**
 * A table for any list. Each column says how to show one row, e.g.
 * `{ header: 'Name', render: (client) => <Link to={...}>{client.name}</Link> }`.
 */
export default function DataTable<T extends { id: number }>({ columns, rows }: DataTableProps<T>) {
  return (
    <div className="overflow-x-auto rounded-lg border border-slate-800 bg-slate-900">
      <table className="min-w-full divide-y divide-slate-800 text-sm">
        <thead className="bg-slate-950">
          <tr>
            {columns.map((column) => (
              <th key={column.header} className="px-4 py-3 text-left font-medium whitespace-nowrap text-slate-400">
                {column.header}
              </th>
            ))}
          </tr>
        </thead>
        <tbody className="divide-y divide-slate-800">
          {rows.map((row) => (
            <tr key={row.id}>
              {columns.map((column) => (
                <td key={column.header} className="px-4 py-3 text-slate-300">
                  {column.render(row)}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
