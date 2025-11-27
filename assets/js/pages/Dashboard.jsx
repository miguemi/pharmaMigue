import { Link } from "@inertiajs/react";
import MainLayout from "./Layouts/MainLayout";

export default function Dashboard({ user }) {
    return (
        <MainLayout user={user}>
            <h1 className="text-2xl font-bold text-gray-800 mb-6">
                Dashboard
            </h1>

            {/* Tarjetas de información */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div className="bg-white rounded-lg shadow p-6">
                    <h3 className="text-gray-500 text-sm uppercase">
                        Usuario
                    </h3>
                    <p className="text-2xl font-bold text-gray-800">
                        {user.name}
                    </p>
                    <p className="text-gray-600">{user.email}</p>
                </div>

                <div className="bg-white rounded-lg shadow p-6">
                    <h3 className="text-gray-500 text-sm uppercase">
                        Rol
                    </h3>
                    <p className="text-2xl font-bold text-gray-800">
                        {user.isAdmin ? "Administrador" : "Usuario"}
                    </p>
                </div>

                <div className="bg-white rounded-lg shadow p-6">
                    <h3 className="text-gray-500 text-sm uppercase">
                        Tenants Asignados
                    </h3>
                    <p className="text-2xl font-bold text-gray-800">
                        {user.tenants.length}
                    </p>
                    <div className="mt-2">
                        {user.tenants.map((tenant) => (
                            <span
                                key={tenant.id}
                                className="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mr-1"
                            >
                                {tenant.code}
                            </span>
                        ))}
                    </div>
                </div>
            </div>

            {/* Accesos rápidos */}
            <div className="bg-white rounded-lg shadow p-6">
                <h2 className="text-lg font-bold text-gray-800 mb-4">
                    Accesos Rápidos
                </h2>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {user.isAdmin && (
                        <Link
                            href="/users"
                            className="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100"
                        >
                            <span className="text-3xl mb-2">👥</span>
                            <span className="text-gray-700">Usuarios</span>
                        </Link>
                    )}

                    {user.tenants.map((tenant) => (
                        <Link
                            key={tenant.id}
                            href={`/tenant/${tenant.code}/products`}
                            className="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100"
                        >
                            <span className="text-3xl mb-2">🏪</span>
                            <span className="text-gray-700">{tenant.name}</span>
                        </Link>
                    ))}
                </div>
            </div>
        </MainLayout>
    );
}
