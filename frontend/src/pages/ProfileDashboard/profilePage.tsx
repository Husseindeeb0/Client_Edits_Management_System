import ChangePasswordForm from '../../components/ProfileComponents/ChangePasswordForm';
import ProfileForm from '../../components/ProfileComponents/ProfileForm';
import { useAuth } from '../../hooks/useAuth';

export default function ProfilePage() {
  // ProtectedRoute only renders this page once the admin is loaded
  const { user } = useAuth();

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-semibold">Profile</h1>

      {/* Side by side on large screens, stacked on small ones */}
      <div className="grid items-start gap-6 lg:grid-cols-2">
        <section className="rounded-lg border border-slate-800 bg-slate-900 p-6">
          <h2 className="text-base font-semibold text-slate-100">Account details</h2>
          <p className="mt-1 mb-6 text-sm text-slate-400">The email is the one you log in with.</p>
          {user && <ProfileForm user={user} />}
        </section>

        <section className="rounded-lg border border-slate-800 bg-slate-900 p-6">
          <h2 className="text-base font-semibold text-slate-100">Change password</h2>
          <p className="mt-1 mb-6 text-sm text-slate-400">At least 8 characters, different from the current one.</p>
          <ChangePasswordForm />
        </section>
      </div>
    </div>
  );
}
