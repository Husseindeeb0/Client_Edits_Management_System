import { useGetClientSummaryQuery } from '../../client/clientApi';
import { formatMoney } from '../../lib/format';

export default function ClientSummaryCards({ clientId }: { clientId: number }) {
  const { data: summary } = useGetClientSummaryQuery(clientId);

  const cards = [
    { label: 'Companies', value: summary?.companies_count },
    { label: 'Projects', value: summary?.projects_count },
    { label: 'Active edits', value: summary?.active_edits_count },
    { label: 'Unpaid total', value: summary ? formatMoney(summary.unpaid_total) : undefined },
  ];

  return (
    <div className="grid grid-cols-2 gap-3 lg:grid-cols-4">
      {cards.map((card) => (
        <div key={card.label} className="rounded-lg border border-slate-800 bg-slate-900 p-4">
          <p className="text-sm text-slate-400">{card.label}</p>
          <p className="mt-1 text-2xl font-semibold text-slate-100">{card.value ?? '–'}</p>
        </div>
      ))}
    </div>
  );
}
