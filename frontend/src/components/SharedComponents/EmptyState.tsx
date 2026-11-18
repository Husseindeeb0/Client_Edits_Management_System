export default function EmptyState({ message }: { message: string }) {
  return (
    <p className="rounded-lg border border-dashed border-slate-700 bg-slate-900 py-12 text-center text-sm text-slate-400">
      {message}
    </p>
  );
}
