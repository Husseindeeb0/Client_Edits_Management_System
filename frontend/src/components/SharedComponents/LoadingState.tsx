import { LoaderCircle } from 'lucide-react';

export default function LoadingState() {
  return (
    <div className="flex justify-center py-12">
      <LoaderCircle className="size-6 animate-spin text-slate-500" aria-label="Loading" />
    </div>
  );
}
