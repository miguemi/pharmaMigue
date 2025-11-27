import { Link, router } from "@inertiajs/react";
import MainLayout from "../Layouts/MainLayout";

export default function Index({ user, tenant, categories }) {
    const handleDelete = (id) => {
        if (confirm("¿Estás seguro de eliminar esta categoría?")) {
            router.post(`/tenant/${tenant.code}/categories/${id}/delete`);
        }
    };

    return (
        <MainLayout user={user} currentTenant={tenant}>
            <div className="flex justify-between items-center mb-6">
                <h1 className="text-2xl font-bold">Categorías</h1>
                <Link
                    href={`/tenant/${tenant.code}/categories/create`}
                    className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                >
                    Nueva Categoría
                </Link>
            </div>

            <div className="bg-white shadow rounded-lg overflow-hidden">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Nombre
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Descripción
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Estado
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                        {categories.map((category) => (
                            <tr key={category.id}>
                                <td className="px-6 py-4 whitespace-nowrap font-medium">
                                    {category.name}
                                </td>
                                <td className="px-6 py-4">
                                    {category.description || "-"}
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <span
                                        className={`px-2 py-1 text-xs rounded ${
                                            category.isActive
                                                ? "bg-green-100 text-green-800"
                                                : "bg-red-100 text-red-800"
                                        }`}
                                    >
                                        {category.isActive ? "Activa" : "Inactiva"}
                                    </span>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">
                                    <Link
                                        href={`/tenant/${tenant.code}/categories/${category.id}/edit`}
                                        className="text-blue-600 hover:text-blue-900 mr-3"
                                    >
                                        Editar
                                    </Link>
                                    <button
                                        onClick={() => handleDelete(category.id)}
                                        className="text-red-600 hover:text-red-900"
                                    >
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>

                {categories.length === 0 && (
                    <div className="text-center py-8 text-gray-500">
                        No hay categorías registradas
                    </div>
                )}
            </div>
        </MainLayout>
    );
}
