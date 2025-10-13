const Input = ({ label, error, type = "text", ...props }) => {
    return (
        <fieldset className="fieldset">
            {label && <legend className="fieldset-legend">{label}</legend>}
            <input
                type={type}
                className="placeholder:text-slate-400  text-sm border border-slate-200 rounded-md px-3 py-2 transition duration-300 ease focus:outline-none focus:border-slate-400 hover:border-slate-300 shadow-sm focus:shadow"
                {...props}
            />
            {error && <span className="fieldset-label text-error">{error}</span>}
        </fieldset>
    );
};

export default Input;
