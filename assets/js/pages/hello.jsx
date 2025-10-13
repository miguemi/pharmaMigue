import { useState } from "react";
import Input from "@/Components/Input";

function SimpleButton({ onClick, children, disabled }) {
    return (
        <button
            type="button"
            className={`px-4 py-2 rounded text-white focus:outline-none focus:ring-2 focus:ring-blue-400
                        ${disabled ? "bg-gray-400 cursor-not-allowed" : "bg-blue-600 hover:bg-blue-700 active:bg-blue-800"}`}
            onClick={onClick}
            disabled={disabled}
        >
            {children}
        </button>
    );
    function buttonsave() {
        return (
            <button
                type="button"
                className="text-white bg-green-700 hover:bg-green-800 focus:outline-none
            focus:ring-4 focus:ring-green-300 font-medium rounded-full text-sm px-5 py-2.5
            text-center me-2 mb-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800"
            ></button>
        );
    }
}

const Hello = ({ productos }) => {
    const [count, setCount] = useState(0);
    const [name, setName] = useState("");
    const [category, setCategory] = useState("");
    const [price, setPrice] = useState("");
    const [isSaving, setIsSaving] = useState(false);

    const handleSaveProduct = async (e) => {
        e.preventDefault(); // Evita que el formulario recargue la página
        if (isSaving) return; // Evita múltiples clics

        setIsSaving(true);
        try {
            const response = await fetch(
                `/save/${encodeURIComponent(name)}/${encodeURIComponent(category)}/${encodeURIComponent(price)}`,
                { method: "POST" },
            );
            if (!response.ok) throw new Error("Error saving product");
        } catch (error) {
            console.error(error);
        } finally {
            setIsSaving(false);
        }
    };

    return (
        <main className="container mx-auto my-3">
            <h1 className="text-3xl font-bold mb-3 text-center">
                Welcome to my pharmacy Miguemi!
            </h1>
            <p className="text-2xl mb-3">
                Count: <strong>{count}</strong>
            </p>

            <div className="flex flex-row gap-3 mb-5">
                <SimpleButton onClick={() => setCount((prev) => prev + 1)}>
                    Add +
                </SimpleButton>
                <SimpleButton onClick={() => setCount((prev) => prev - 1)}>
                    Subtract -
                </SimpleButton>
            </div>

            <form className="mb-6 flex flex-col gap-3 items-center">
                <Input
                    label="Product Name"
                    type="text"
                    placeholder="Add Product Name"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                />
                <Input
                    label="Category"
                    type="text"
                    placeholder="Add Category"
                    value={category}
                    onChange={(e) => setCategory(e.target.value)}
                />
                <Input
                    label="Price"
                    type="number"
                    placeholder="Add Price"
                    value={price}
                    onChange={(e) => setPrice(e.target.value)}
                />
                <button
                    type="button"
                    class="text-white bg-green-700 hover:bg-green-800 font-medium rounded-full text-sm px-5 py-2.5 text-center"
                    onClick={handleSaveProduct}
                    disabled={isSaving}
                >
                    {isSaving ? "Saving..." : "Save Product"}
                </button>
            </form>
        </main>
    );
};

export default Hello;
