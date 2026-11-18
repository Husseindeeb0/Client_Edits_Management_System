const TONE_CLASSES = {
  gray: 'bg-slate-800 text-slate-300',
  blue: 'bg-blue-500/15 text-blue-300',
  yellow: 'bg-amber-500/15 text-amber-300',
  green: 'bg-green-500/15 text-green-300',
  red: 'bg-red-500/15 text-red-300',
};

interface BadgeProps {
  label: string;
  tone?: keyof typeof TONE_CLASSES;
}

/** A small colored label, e.g. an edit's status, priority or paid state. */
export default function Badge({ label, tone = 'gray' }: BadgeProps) {
  return (
    <span className={`inline-block rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap ${TONE_CLASSES[tone]}`}>
      {label}
    </span>
  );
}
