import { LoaderCircle } from 'lucide-react';

export default function LoadingState() {
  return (
    <div className="flex min-h-[50vh] items-center justify-center">
      <LoaderCircle className="size-6 animate-spin text-slate-500" aria-label="Loading" />
    </div>
  );
}
